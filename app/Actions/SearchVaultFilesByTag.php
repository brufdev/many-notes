<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Database\Eloquent\Builder;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Collection;

final readonly class SearchVaultFilesByTag
{
    /** @return Collection<int, VaultNode> */
    public function handle(Vault $vault, string $tag): Collection
    {
        return $vault->nodes()
            ->select('id', 'name', 'extension', 'updated_at')
            ->where('is_file', true)
            ->whereHas('tags', fn(Builder $query): Builder => $query->where('name', $tag))
            ->orderByDesc('updated_at')
            ->get();
    }
}
