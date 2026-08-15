<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Tag;
use App\Models\VaultNode;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
    public function getLinks(VaultNode $file): Collection
    {
        $totals = $this->countOccurrences('vault_node_vault_node', 'destination_id', 'source_id', $file->id);

        /** @var Collection<int, VaultNode&object{total: int}> */
        return VaultNode::query()
            ->select('vault_nodes.*', 'link_totals.total')
            ->joinSub($totals, 'link_totals', 'vault_nodes.id', '=', 'link_totals.destination_id')
            ->get();
    }

    /** @return Collection<int, VaultNode&object{total: int}> */
    public function getBacklinks(VaultNode $file): Collection
    {
        $totals = $this->countOccurrences('vault_node_vault_node', 'source_id', 'destination_id', $file->id);

        /** @var Collection<int, VaultNode&object{total: int}> */
        return VaultNode::query()
            ->select('vault_nodes.*', 'backlink_totals.total')
            ->joinSub($totals, 'backlink_totals', 'vault_nodes.id', '=', 'backlink_totals.source_id')
            ->get();
    }

    /** @return SupportCollection<int, Tag&object{total: int}> */
    public function getTags(VaultNode $file): SupportCollection
    {
        $totals = $this->countOccurrences('tag_vault_node', 'tag_id', 'vault_node_id', $file->id);

        /** @var SupportCollection<int, Tag&object{total: int}> */
        return Tag::query()
            ->select('tags.*', 'tag_totals.total')
            ->joinSub($totals, 'tag_totals', 'tags.id', '=', 'tag_totals.tag_id')
            ->orderBy('tags.name')
            ->get();
    }

    private function countOccurrences(
        string $pivotTable,
        string $countedKey,
        string $filteredKey,
        int $nodeId,
    ): QueryBuilder {
        return DB::table($pivotTable)
            ->select($countedKey, DB::raw('count(*) as total'))
            ->where($filteredKey, $nodeId)
            ->groupBy($countedKey);
    }
}
