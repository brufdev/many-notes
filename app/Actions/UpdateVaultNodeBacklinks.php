<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\VaultNode;
use Illuminate\Support\Facades\DB;

final readonly class UpdateVaultNodeBacklinks
{
    public function handle(VaultNode $node): void
    {
        $targets = $node->is_file
            ? collect([$node])
            : $node->descendants()->where('is_file', true)->get();

        /** @var array<int, string> $linkPaths */
        $linkPaths = $targets
            ->mapWithKeys(fn(VaultNode $target): array => [
                $target->id => "/{$target->fullPath()}.{$target->extension}",
            ])
            ->all();

        $linksBySource = DB::table('vault_node_vault_node')
            ->whereIn('destination_id', array_keys($linkPaths))
            ->get(['source_id', 'destination_id', 'position'])
            ->groupBy('source_id');

        if ($linksBySource->isEmpty()) {
            return;
        }

        $sources = $node->vault->nodes()->whereIn('id', $linksBySource->keys())->get();
        $getLinksFromContent = app(GetLinksFromContent::class);
        $updateVaultNode = app(UpdateVaultNode::class);

        foreach ($sources as $source) {
            /** @var array<int, int> $destinations */
            $destinations = $linksBySource->get($source->id, collect())->pluck('destination_id', 'position')->all();
            $content = (string) $source->content;

            // Replace from the end so earlier positions stay valid
            foreach (array_reverse($getLinksFromContent->handle($content)) as $link) {
                $destinationId = $destinations[$link['position']] ?? null;

                if ($destinationId === null) {
                    continue;
                }

                $content = substr_replace(
                    $content,
                    $linkPaths[$destinationId],
                    $link['path_position'],
                    mb_strlen($link['path'], '8bit'),
                );
            }

            $updateVaultNode->handle($source, ['content' => $content], true);
        }
    }
}
