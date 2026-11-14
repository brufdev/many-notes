<?php

declare(strict_types=1);

namespace App\Actions;

final readonly class GetLinksFromContent
{
    /** @return list<array{position: int, path: string, path_position: int}> */
    public function handle(string $content): array
    {
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
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        return array_map(fn(array $match): array => [
            'position' => $match[0][1],
            'path' => $match[1][0],
            'path_position' => $match[1][1],
        ], $matches);
    }
}
