<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\VaultNodeType;
use App\Models\Vault;
use App\Models\VaultNode;

final readonly class SearchVaultFilesByText
{
    /**
     * @return list<
     *   array{
     *     id: string,
     *     type: VaultNodeType,
     *     name: string,
     *     content: string,
     *     extension: string,
     *     updated_at: string
     *   }
     * >
     */
    public function handle(Vault $vault, string $search): array
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
         * }
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

        $results = [];

        foreach ($rawResults['hits'] as $hit) {
            if ($nodes->has($hit['document']['id'])) {
                /** @var VaultNode $node */
                $node = $nodes->get($hit['document']['id']);

                $results[] = [
                    'id' => $hit['document']['id'],
                    'type' => $node->type(),
                    'name' => isset($hit['highlight']['name'])
                        ? $hit['highlight']['name']['snippet']
                        : $hit['document']['name'],
                    'content' => isset($hit['highlight']['content'])
                        ? $hit['highlight']['content']['snippet']
                        : $hit['document']['content'],
                    'extension' => $node->extension ?? '',
                    'updated_at' => $hit['document']['updated_at'],
                ];
            }
        }

        return $results;
    }
}
