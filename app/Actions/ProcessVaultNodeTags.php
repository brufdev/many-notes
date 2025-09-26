<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Tag;
use App\Models\VaultNode;

final readonly class ProcessVaultNodeTags
{
    public function handle(VaultNode $node): void
    {
        $node->tags()->detach();

        if ((string) $node->content === '') {
            return;
        }

        /** @var string $content */
        $content = $node->content;

        // Find tags
        preg_match_all('/(?:^|\s)(#[\p{L}0-9_-]+)/u', $content, $matches, PREG_OFFSET_CAPTURE);

        if ($matches[1] === []) {
            return;
        }

        $allTags = $matches[1];

        // Find inline codes and codeblocks start and end positions
        preg_match_all('/`[^`\n]+`|```[^(?:```)]+```/u', $content, $matches, PREG_OFFSET_CAPTURE);
        $codePositions = array_map(
            fn(array $match): array => [
                'start' => $match[1],
                'end' => $match[1] + mb_strlen($match[0]),
            ],
            $matches[0],
        );

        // Filter out tags that are inside code blocks
        $filteredTags = array_filter(
            $allTags,
            fn(array $tag): bool => array_all(
                $codePositions,
                fn(array $position): bool => $tag[1] < $position['start'] || $tag[1] > $position['end'],
            ),
        );

        foreach ($filteredTags as $filteredTag) {
            $tag = Tag::firstOrCreate([
                'name' => mb_substr($filteredTag[0], 1),
            ]);

            $node->tags()->attach($tag->id, ['position' => $filteredTag[1]]);
        }
    }
}
