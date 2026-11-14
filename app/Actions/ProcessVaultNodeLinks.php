<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\VaultNode;
use Illuminate\Support\Facades\DB;

final readonly class ProcessVaultNodeLinks
{
    public function handle(VaultNode $node): void
    {
        $rows = $this->buildLinkRows($node);

        DB::transaction(function () use ($node, $rows): void {
            $node->links()->detach();
            $node->links()->newPivotStatement()->insert($rows);
        });
    }

    /** @return list<array{source_id: int, destination_id: int, position: int}> */
    private function buildLinkRows(VaultNode $node): array
    {
        if ($node->content === null || $node->content === '') {
            return [];
        }

        $links = app(GetLinksFromContent::class)->handle($node->content);

        if ($links === []) {
            return [];
        }

        $vault = $node->loadMissing('vault')->vault;
        $resolveTwoPaths = app(ResolveTwoPaths::class);
        $getVaultNodeFromPath = app(GetVaultNodeFromPath::class);

        /** @phpstan-ignore-next-line larastan.noUnnecessaryCollectionCall */
        $fullPath = $node->ancestorsAndSelf()->get()->last()->full_path;
        $rows = [];

        foreach ($links as $link) {
            $path = $resolveTwoPaths->handle($fullPath, $link['path']);
            $destinationNode = $getVaultNodeFromPath->handle($vault, $path);

            if (is_null($destinationNode)) {
                continue;
            }

            $rows[] = [
                'source_id' => $node->id,
                'destination_id' => $destinationNode->id,
                'position' => $link['position'],
            ];
        }

        return $rows;
    }
}
