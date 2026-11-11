<?php

declare(strict_types=1);

use App\Services\UnicodePathNormalizer;
use League\Flysystem\CorruptedPathDetected;
use League\Flysystem\PathTraversalDetected;

it('normalizes paths', function (string $path, string $expected): void {
    expect(app(UnicodePathNormalizer::class)->normalizePath($path))->toBe($expected);
})->with([
    'plain' => ['vaults/1/Notes/Plan.md', 'vaults/1/Notes/Plan.md'],
    'backslashes' => ['vaults\\1\\Plan.md', 'vaults/1/Plan.md'],
    'empty and current folder segments' => ['/vaults//1/./Plan.md/', 'vaults/1/Plan.md'],
    'emoji sequence' => ["vaults/1/\u{1F468}\u{200D}\u{1F4BB} Work.md", "vaults/1/\u{1F468}\u{200D}\u{1F4BB} Work.md"],
    'soft hyphen' => ["vaults/1/co\u{00AD}operate.md", "vaults/1/co\u{00AD}operate.md"],
    'right-to-left override' => ["vaults/1/\u{202E}Plan.md", "vaults/1/\u{202E}Plan.md"],
]);

it('rejects corrupted paths', function (string $path): void {
    app(UnicodePathNormalizer::class)->normalizePath($path);
})->throws(CorruptedPathDetected::class)->with([
    'null byte' => "vaults/1/Plan\0.md",
    'tab' => "vaults/1/Plan\t.md",
    'line feed' => "vaults/1/Plan\n.md",
    'delete' => "vaults/1/Plan\x7F.md",
    'invalid utf-8' => "vaults/1/Plan\xFF.md",
]);

it('rejects parent folder segments', function (string $path): void {
    app(UnicodePathNormalizer::class)->normalizePath($path);
})->throws(PathTraversalDetected::class)->with([
    '../Plan.md',
    'vaults/../../Plan.md',
    'vaults/1/Notes/../Plan.md',
    'vaults/1/Notes/..',
    'vaults\\1\\..\\Plan.md',
]);
