<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;
use App\Models\VaultNode;
use App\Services\VaultFiles\Types\Image;
use Illuminate\Database\Eloquent\Builder;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Collection;

final readonly class SearchVaultFilesForEditor
{
    /** @return Collection<int, VaultNode> */
    public function handle(Vault $vault, string $search, string $searchType): Collection
    {
        return VaultNode::query()
            ->where('vault_id', $vault->id)
            ->where('is_file', true)
            ->when($searchType === 'image', function (Builder $query): void {
                $query->whereIn('extension', Image::extensions());
            })
            ->when(mb_strlen($search), function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderByDesc('updated_at')
            ->get();
    }
}
