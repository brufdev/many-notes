<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Support\Collection as SupportCollection;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Collection;

final readonly class GetVaultOpenedFileTreeData
{
    /**
     * @return array{
     *   ancestors: Collection<int, VaultNode>,
     *   ancestorsChildren: SupportCollection<int, Collection<int, VaultNode>>|null,
     *   siblings: Collection<int, VaultNode>,
     * }
     */
    public function handle(Vault $vault, VaultNode $file): array
    {
        $ancestors = $file
            ->ancestors()
            ->get();

        $ancestorsChildren = null;
        $ancestorsIds = $ancestors->pluck('id')->all();

        if ($ancestorsIds !== []) {
            /** @var SupportCollection<int, Collection<int, VaultNode>> $ancestorsChildren */
            $ancestorsChildren = $vault
                ->nodes()
                ->whereIn('parent_id', $ancestorsIds)
                ->orderBy('parent_id')
                ->orderBy('name')
                ->get()
                ->groupBy('parent_id');
        }

        $siblings = $vault
            ->nodes()
            ->where('parent_id', $file->parent_id)
            ->get();

        return [
            'ancestors' => $ancestors,
            'ancestorsChildren' => $ancestorsChildren,
            'siblings' => $siblings,
        ];
    }
}
