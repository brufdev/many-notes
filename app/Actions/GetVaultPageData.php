<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Tag;
use App\Models\Vault;
use App\Models\VaultNode;
use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Collection;

final readonly class GetVaultPageData
{
    /**
     * @return array{
     *   recentFiles: Closure(): Collection<int, VaultNode>,
     *   rootNodes: Closure(): Collection<int, VaultNode>,
     *   templateNodes: Closure(): (Collection<int, VaultNode>|null),
     *   tags: Closure(): EloquentCollection<int, Tag&object{total: int}>
     * }
     */
    public function handle(Vault $vault): array
    {
        return [
            'recentFiles' => fn(): Collection => $this->getRecentFiles($vault),
            'rootNodes' => fn(): Collection => $this->getRootNodes($vault),
            'templateNodes' => fn(): ?Collection => $this->getTemplateNodes($vault),
            'tags' => fn(): EloquentCollection => $this->getTags($vault),
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
        return app(GetVaultTags::class)->handle($vault);
    }
}
