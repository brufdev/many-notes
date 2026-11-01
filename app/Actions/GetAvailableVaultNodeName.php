<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Database\Eloquent\Builder;

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

        if (!$this->isTaken($siblings, $name)) {
            return $name;
        }

        $getSuffixedName = app(GetSuffixedName::class);
        $number = 1;

        while ($this->isTaken($siblings, $getSuffixedName->handle($name, $number, $extension))) {
            $number++;
        }

        return $getSuffixedName->handle($name, $number, $extension);
    }

    /** @param Builder<VaultNode> $siblings */
    private function isTaken(Builder $siblings, string $name): bool
    {
        return $siblings
            ->clone()
            ->whereRaw('LOWER(name) = LOWER(?)', [$name])
            ->exists();
    }

    /** @return Builder<VaultNode> */
    private function siblings(
        Vault $vault,
        ?int $parentId,
        bool $isFile,
        ?string $extension,
        ?int $ignoreId,
    ): Builder {
        $siblings = VaultNode::query()
            ->where('vault_id', $vault->id)
            ->where('parent_id', $parentId)
            ->where('is_file', $isFile)
            ->where('extension', $extension);

        if ($ignoreId !== null) {
            $siblings->whereKeyNot($ignoreId);
        }

        return $siblings;
    }
}
