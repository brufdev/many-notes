<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Tag;
use App\Models\VaultNode;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Collection;

final readonly class GetVaultOpenedFileData
{
    /**
     * @return array{
     *   links: Collection<int, VaultNode&object{total: int}>,
     *   backlinks: Collection<int, VaultNode&object{total: int}>,
     *   tags: SupportCollection<int, Tag&object{total: int}>
     * }
     */
    public function handle(VaultNode $file): array
    {
        return [
            'links' => $this->getLinks($file),
            'backlinks' => $this->getBacklinks($file),
            'tags' => $this->getTags($file),
        ];
    }

    /** @return Collection<int, VaultNode&object{total: int}> */
    private function getLinks(VaultNode $file): Collection
    {
        /** @var Collection<int, VaultNode&object{total: int}> */
        return $file
            ->links()
            ->select(DB::raw('vault_nodes.*, count(*) as total'))
            ->groupBy(
                'id',
                'name',
                'vault_node_vault_node.source_id',
                'vault_node_vault_node.destination_id',
                'vault_node_vault_node.position',
            )
            ->get();
    }

    /** @return Collection<int, VaultNode&object{total: int}> */
    private function getBacklinks(VaultNode $file): Collection
    {
        /** @var Collection<int, VaultNode&object{total: int}> */
        return $file
            ->backlinks()
            ->select(DB::raw('vault_nodes.*, count(*) as total'))
            ->groupBy(
                'id',
                'name',
                'vault_node_vault_node.destination_id',
                'vault_node_vault_node.source_id',
                'vault_node_vault_node.position',
            )
            ->get();
    }

    /** @return SupportCollection<int, Tag&object{total: int}> */
    private function getTags(VaultNode $file): SupportCollection
    {
        /** @var SupportCollection<int, Tag&object{total: int}> */
        return $file
            ->tags()
            ->select(DB::raw('tags.*, count(*) as total'))
            ->groupBy(
                'tags.id',
                'tags.name',
                'tag_vault_node.vault_node_id',
                'tag_vault_node.tag_id',
                'vault_node_vault_node.position',
            )
            ->orderBy('tags.name')
            ->get();
    }
}
