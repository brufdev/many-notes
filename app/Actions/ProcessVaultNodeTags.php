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
        preg_match_all('/#[\p{L}0-9_-]+/u', $content, $matches, PREG_OFFSET_CAPTURE);

        if ($matches[0] === []) {
            return;
        }

        foreach ($matches[0] as $match) {
            $tag = Tag::firstOrCreate([
                'name' => mb_substr($match[0], 1),
            ]);

            $node->tags()->attach($tag->id, ['position' => $match[1]]);
        }
    }
}
