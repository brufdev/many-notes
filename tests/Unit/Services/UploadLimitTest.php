<?php

declare(strict_types=1);

use App\Services\UploadLimit;

it('formats bytes like Number::fileSize()', function (int $bytes, string $expected): void {
    expect(UploadLimit::format($bytes))->toBe($expected);
})->with([
    [0, '0 B'],
    [921, '921 B'],
    [922, '1 KB'],
    [1024, '1 KB'],
    [1536, '2 KB'],
    [2560, '2 KB'],
    [3584, '4 KB'],
    [500 * 1024 ** 2, '500 MB'],
    [2 * 1024 ** 3, '2 GB'],
    [1024 ** 4, '1 TB'],
]);
