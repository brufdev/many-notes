<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Tag;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Collection;

final readonly class GetVaultPageData
{
    /**
     * @return array{
     *   recentFiles: Collection<int, VaultNode>,
     *   rootNodes: Collection<int, VaultNode>,
     *   templateNodes: Collection<int, VaultNode>|null,
     *   tags: EloquentCollection<int, Tag&object{total: int}>
     * }
     */
    public function handle(Vault $vault): array
    {
        return [
            'recentFiles' => $this->getRecentFiles($vault),
            'rootNodes' => $this->getRootNodes($vault),
            'templateNodes' => $this->getTemplateNodes($vault),
            'tags' => $this->getTags($vault),
        ];
    }

    /** @return Collection<int, VaultNode> */
    private function getRecentFiles(Vault $vault): Collection
    {
        return VaultNode::query()
            ->where('vault_id', $vault->id)
            ->where('is_file', true)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
    }

    /** @return Collection<int, VaultNode> */
    private function getRootNodes(Vault $vault): Collection
    {
        return $vault
            ->nodes()
            ->whereNull('parent_id')
            ->get();
    }

    /** @return Collection<int, VaultNode>|null */
    private function getTemplateNodes(Vault $vault): ?Collection
    {
        return $vault
            ->templatesNode
            ?->descendants()
            ->where('is_file', true)
            ->where('extension', 'md')
            ->orderBy('name')
            ->get();
    }

    /** @return EloquentCollection<int, Tag&object{total: int}> */
    private function getTags(Vault $vault): EloquentCollection
    {
        /** @var EloquentCollection<int, Tag&object{total: int}> */
        return $vault
            ->tags()
            ->select(DB::raw('tags.*, count(*) AS total'))
            ->groupBy('tags.id', 'tags.name', 'vault_nodes.vault_id')
            ->orderBy('tags.name')
            ->get();
    }
}
