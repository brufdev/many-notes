<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;
use App\Models\VaultNode;
use App\ViewModels\VaultFileSearchHitModel;
use App\ViewModels\VaultFileSearchViewModel;
use Illuminate\Database\Eloquent\Builder;

final readonly class SearchVaultFiles
{
    /** @return array<int, array<string, mixed>> */
    public function handle(Vault $vault, string $search): array
    {
        preg_match('/tag:([\p{L}0-9_-]+)/u', $search, $matches);

        return $matches === []
            ? $this->searchText($vault, $search)
            : $this->searchTag($vault, $matches[1]);
    }

    /** @return array<int, array<string, mixed>> */
    private function searchText(Vault $vault, string $search): array
    {
        /**
         * @var array{
         *   hits: list<
         *     array{
         *       document: array{
         *         id: string,
         *         name: string,
         *         content: string,
         *         updated_at: string
         *       },
         *       highlight: array{
         *         name?: array{
         *           snippet: string
         *         },
         *         content?: array{
         *           snippet: string
         *         }
         *       }
         *     }
         *   >
         * } $rawResults
         */
        $rawResults = VaultNode::search($search)
            ->where('vault_id', $vault->id)
            ->raw();

        $ids = collect($rawResults['hits'])
            ->pluck('document.id')
            ->all();

        $nodes = VaultNode::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return collect($rawResults['hits'])
            ->filter(fn(array $hit): bool => $nodes->has($hit['document']['id']))
            ->map(function (array $hit) use ($nodes): array {
                $id = $hit['document']['id'];

                /** @var VaultNode $node */
                $node = $nodes->get($id);

                $hitModel = VaultFileSearchHitModel::fromRaw($hit, $node);

                return VaultFileSearchViewModel::fromTextSearch($hitModel)->toArray();
            })
            ->values()
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private function searchTag(Vault $vault, string $tag): array
    {
        $results = [];

        $files = $vault->nodes()
            ->select('id', 'name', 'extension', 'updated_at')
            ->where('is_file', true)
            ->whereHas('tags', fn(Builder $query): Builder => $query->where('name', $tag))
            ->orderByDesc('updated_at')
            ->get();

        foreach ($files as $file) {
            $results[] = VaultFileSearchViewModel::fromTagSearch($file)->toArray();
        }

        return $results;
    }
}
