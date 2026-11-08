<?php

declare(strict_types=1);

use App\Actions\IsSupportedFile;

it('detects supported files', function (string $fileName, ?string $mimeType): void {
    expect(app(IsSupportedFile::class)->handle($fileName, $mimeType))->toBeTrue();
})->with([
    'note' => ['Plan.md', 'text/plain'],
    'image' => ['photo.png', 'image/png'],
    'name checked before the content is read' => ['photo.png', null],
]);

it('detects unsupported files', function (string $fileName, ?string $mimeType): void {
    expect(app(IsSupportedFile::class)->handle($fileName, $mimeType))->toBeFalse();
})->with([
    'unsupported extension' => ['Board.canvas', null],
    'no extension' => ['README', null],
    'uppercase extension' => ['photo.PNG', 'image/png'],
    'content that does not match the extension' => ['photo.png', 'text/html'],
]);
