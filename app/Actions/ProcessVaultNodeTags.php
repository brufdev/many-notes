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
        $codeRanges = app(GetCodeRangesFromContent::class)->handle($content);

        // Filter out tags that are inside code
        $filteredTags = array_filter(
            $allTags,
            fn(array $tag): bool => !array_any(
                $codeRanges,
                fn(array $range): bool => $tag[1] >= $range['start'] && $tag[1] < $range['end'],
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
