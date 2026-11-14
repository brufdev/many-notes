<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\VaultNode;
use Illuminate\Support\Facades\DB;

final readonly class UpdateVaultNodeLinks
{
    public function handle(VaultNode $node): void
    {
        $vault = $node->vault;
        $movedIds = $node->is_file
            ? [$node->id]
            : $node->descendants()->where('is_file', true)->pluck('id')->all();

        // Links to and from the moved notes, by the note that holds them
        $links = DB::table('vault_node_vault_node')
            ->whereIn('destination_id', $movedIds)
            ->orWhereIn('source_id', $movedIds)
            ->get(['source_id', 'destination_id', 'position']);

        if ($links->isEmpty()) {
            return;
        }

        $linksBySource = $links->groupBy('source_id');

        /** @var array<int, string> $linkPaths */
        $linkPaths = $vault->nodes()
            ->whereIn('id', $links->pluck('destination_id')->unique())
            ->get()
            ->mapWithKeys(fn(VaultNode $destination): array => [
                $destination->id => "/{$destination->fullPath()}.{$destination->extension}",
            ])
            ->all();

        $sources = $vault->nodes()->whereIn('id', $linksBySource->keys())->get();
        $getLinksFromContent = app(GetLinksFromContent::class);
        $resolveTwoPaths = app(ResolveTwoPaths::class);
        $getVaultNodeFromPath = app(GetVaultNodeFromPath::class);
        $updateVaultNode = app(UpdateVaultNode::class);

        foreach ($sources as $source) {
            /** @var array<int, int> $destinations */
            $destinations = $linksBySource->get($source->id, collect())->pluck('destination_id', 'position')->all();
            $sourcePath = $source->fullPath();
            $content = (string) $source->content;

            // Replace from the end so earlier positions stay valid
            foreach (array_reverse($getLinksFromContent->handle($content)) as $link) {
                $destinationId = (int) ($destinations[$link['position']] ?? 0);

                if (!isset($linkPaths[$destinationId])) {
                    continue;
                }

                // Keep links that still resolve to their note
                $resolvedNode = $getVaultNodeFromPath->handle(
                    $vault,
                    $resolveTwoPaths->handle($sourcePath, $link['path']),
                );

                if ($resolvedNode?->id === $destinationId) {
                    continue;
                }

                $content = substr_replace(
                    $content,
                    $linkPaths[$destinationId],
                    $link['path_position'],
                    mb_strlen($link['path'], '8bit'),
                );
            }

            if ($content !== $source->content) {
                $updateVaultNode->handle($source, ['content' => $content], true);
            }
        }
    }
}
