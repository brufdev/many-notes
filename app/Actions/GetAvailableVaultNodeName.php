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
        $taken = $this
            ->siblings($vault, $parentId, $isFile, $extension, $ignoreId)
            ->whereRaw('LOWER(name) = LOWER(?)', [$name])
            ->exists();

        if (!$taken) {
            return $name;
        }

        /** @var list<string> $names */
        $names = $this
            ->siblings($vault, $parentId, $isFile, $extension, $ignoreId)
            ->select('name')
            ->whereRaw('LOWER(name) LIKE LOWER(?)', [$name . '-%'])
            ->pluck('name')
            ->toArray();
        natcasesort($names);

        return $name . (count($names) && preg_match('/-(\d+)$/', end($names), $matches) === 1
            ? '-' . ((int) $matches[1] + 1)
            : '-1');
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
