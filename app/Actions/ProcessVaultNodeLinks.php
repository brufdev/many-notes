<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\VaultNode;
use App\Services\VaultFile;

final readonly class ProcessVaultNodeLinks
{
    public function handle(VaultNode $node): void
    {
        $node->links()->detach();

        if ($node->content === null || $node->content === '') {
            return;
        }

        $extensions = implode('|', VaultFile::extensions());
        $pattern = <<<REGEX
            /
            !?                                 # Optional: Match "!" at the beginning of the link to process images too
            \[.*?\]                            # Match a markdown-style link text [any text]
            \(                                 # Match opening parenthesis "("
                (.*?(?:\.(?:{$extensions}))?)  # Capture group 1: Match a file name with an optional valid extension
                (?:\s".*?")?                   # Optional: Match a title in quotes after the filename
            \)                                 # Match closing parenthesis ")"
            /xi
        REGEX;
        preg_match_all($pattern, (string) $node->content, $matches, PREG_OFFSET_CAPTURE);

        if ($matches[1] === []) {
            return;
        }

        $linkPaths = array_column($matches[1], 0);
        $linkPositions = array_column($matches[0], 1);
        $links = array_map(
            fn(string $path, int $position): array => ['path' => $path, 'position' => $position],
            $linkPaths,
            $linkPositions,
        );

        foreach ($links as $link) {
            /**
             * @var string $fullPath
             *
             * @phpstan-ignore-next-line larastan.noUnnecessaryCollectionCall
             */
            $fullPath = $node->ancestorsAndSelf()->get()->last()->full_path;
            $path = new ResolveTwoPaths()->handle($fullPath, $link['path']);
            $destinationNode = new GetVaultNodeFromPath()->handle($node->vault_id, $path);

            if (is_null($destinationNode)) {
                continue;
            }

            $node->links()->attach($destinationNode->id, ['position' => $link['position']]);
        }
    }
}
