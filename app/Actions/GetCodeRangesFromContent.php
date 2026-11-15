<?php

declare(strict_types=1);

namespace App\Actions;

final readonly class GetCodeRangesFromContent
{
    /** @return list<array{start: int, end: int}> */
    public function handle(string $content): array
    {
        $ranges = $this->fencedBlocks($content);

        // Blank out fenced blocks before finding inline code
        $masked = $content;

        foreach ($ranges as $range) {
            $length = $range['end'] - $range['start'];
            $masked = substr_replace($masked, str_repeat(' ', $length), $range['start'], $length);
        }

        $pattern = <<<'REGEX'
            /
            (?<!`)(`+)(?!`)            # Match an opening run of backticks
            (?:(?!\n[ \t]*\n).)+?      # Match the code, never across a blank line
            (?<!`)\1(?!`)              # Match a closing run of the same length
            /sx
        REGEX;
        preg_match_all($pattern, $masked, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as [$code, $start]) {
            $ranges[] = ['start' => $start, 'end' => $start + mb_strlen($code, '8bit')];
        }

        return $ranges;
    }

    /** @return list<array{start: int, end: int}> */
    private function fencedBlocks(string $content): array
    {
        preg_match_all('/^.*$/m', $content, $lines, PREG_OFFSET_CAPTURE);

        $ranges = [];
        $fence = null;
        $start = 0;

        foreach ($lines[0] as [$line, $lineStart]) {
            if ($fence === null) {
                // Match an opening fence, unless a backtick fence has more backticks on its line
                if (preg_match('/^ {0,3}(`{3,}(?=[^`]*$)|~{3,})/', $line, $match) === 1) {
                    $fence = $match[1];
                    $start = $lineStart;
                }

                continue;
            }

            // Match a closing fence of the same character, at least as long
            $closingFence = sprintf('/^ {0,3}%s{%d,}[ \t]*\r?$/', $fence[0], mb_strlen($fence, '8bit'));

            if (preg_match($closingFence, $line) === 1) {
                $ranges[] = ['start' => $start, 'end' => $lineStart + mb_strlen($line, '8bit')];
                $fence = null;
            }
        }

        // An unclosed fence runs to the end of the note
        if ($fence !== null) {
            $ranges[] = ['start' => $start, 'end' => mb_strlen($content, '8bit')];
        }

        return $ranges;
    }
}
