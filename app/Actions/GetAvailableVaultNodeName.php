<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Scopes\SupportedVaultNodeScope;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

final readonly class GetAvailableVaultNodeName
{
    public function handle(
        Vault $vault,
        ?int $parentId,
        bool $isFile,
        string $name,
        ?string $extension,
        ?int $ignoreId = null,
    ): string {
        $siblings = $this->siblings($vault, $parentId, $isFile, $extension, $ignoreId);
        $directory = $this->directory($vault, $parentId);
        $ignoredPath = $this->ignoredPath($vault, $ignoreId);
        $isTaken = fn(string $candidate): bool => $this->isTaken(
            $siblings,
            $directory . $candidate . ($extension === null ? '' : ".{$extension}"),
            $ignoredPath,
            $candidate,
        );

        if (!$isTaken($name)) {
            return $name;
        }

        $getSuffixedName = app(GetSuffixedName::class);
        $number = 1;

        while ($isTaken($getSuffixedName->handle($name, $number, $extension))) {
            $number++;
        }

        return $getSuffixedName->handle($name, $number, $extension);
    }

    /** @param HasMany<VaultNode, Vault> $siblings */
    private function isTaken(HasMany $siblings, string $path, ?string $ignoredPath, string $name): bool
    {
        if ((clone $siblings)->whereRaw('LOWER(name) = LOWER(?)', [$name])->exists()) {
            return true;
        }

        // Check files without a node
        return mb_strtolower($path) !== $ignoredPath && Storage::disk('local')->exists($path);
    }

    /** @return HasMany<VaultNode, Vault> */
    private function siblings(
        Vault $vault,
        ?int $parentId,
        bool $isFile,
        ?string $extension,
        ?int $ignoreId,
    ): HasMany {
        $siblings = $vault->nodes()
            ->withoutGlobalScope(SupportedVaultNodeScope::class)
            ->where('parent_id', $parentId)
            ->where('is_file', $isFile)
            ->where('extension', $extension);

        if ($ignoreId !== null) {
            $siblings->whereKeyNot($ignoreId);
        }

        return $siblings;
    }

    private function directory(Vault $vault, ?int $parentId): string
    {
        $parent = $parentId === null ? null : $vault->nodes()->find($parentId);

        return app(GetPathFromVaultFolder::class)->handle($vault, $parent);
    }

    private function ignoredPath(Vault $vault, ?int $ignoreId): ?string
    {
        $node = $ignoreId === null
            ? null
            : $vault->nodes()->withoutGlobalScope(SupportedVaultNodeScope::class)->find($ignoreId);

        return $node instanceof VaultNode
            ? mb_strtolower(app(GetPathFromVaultNode::class)->handle($node))
            : null;
    }
}
