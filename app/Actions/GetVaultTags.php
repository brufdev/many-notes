<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Tag;
use App\Models\Vault;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

final readonly class GetVaultTags
{
    /** @return EloquentCollection<int, Tag&object{total: int}> */
    public function handle(Vault $vault): EloquentCollection
    {
        $totals = DB::table('tag_vault_node')
            ->select('tag_vault_node.tag_id', DB::raw('count(*) AS total'))
            ->join('vault_nodes', 'vault_nodes.id', '=', 'tag_vault_node.vault_node_id')
            ->where('vault_nodes.vault_id', $vault->id)
            ->groupBy('tag_vault_node.tag_id');

        /** @var EloquentCollection<int, Tag&object{total: int}> */
        return Tag::query()
            ->select('tags.*', 'tag_totals.total')
            ->joinSub($totals, 'tag_totals', 'tags.id', '=', 'tag_totals.tag_id')
            ->orderBy('tags.name')
            ->get();
    }
}
