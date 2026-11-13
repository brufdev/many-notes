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

        $pattern = <<<'REGEX'
            /
            !?                                 # Optional: Match "!" at the beginning of the link to process images too
            \[.*?\]                            # Match a markdown-style link text [any text]
            \(                                 # Match opening parenthesis "("
                ((?:[^()\n]|\([^()\n]*\))*?)   # Capture group 1: Match the path, allowing balanced parentheses
                (?:\s".*?")?                   # Optional: Match a title in quotes after the path
            \)                                 # Match closing parenthesis ")"
            /xi
        REGEX;
        preg_match_all($pattern, (string) $node->content, $matches, PREG_OFFSET_CAPTURE);

        if ($matches[1] === []) {
            return [];
        }

        $linkPaths = array_column($matches[1], 0);
        $linkPositions = array_column($matches[0], 1);
        $links = array_map(
            fn(string $path, int $position): array => ['path' => $path, 'position' => $position],
            $linkPaths,
            $linkPositions,
        );

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
