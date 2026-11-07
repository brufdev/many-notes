<?php

declare(strict_types=1);

use App\Actions\IsPassthroughFile;

it('shows supported files', function (string $path, ?string $mimeType): void {
    expect(app(IsPassthroughFile::class)->handle($path, $mimeType))->toBeFalse();
})->with([
    'note' => ['Notes/Plan.md', 'text/plain'],
    'image' => ['photo.png', 'image/png'],
    'name checked before the content is read' => ['photo.png', null],
]);

it('keeps hidden and unsupported files on disk only', function (string $path, ?string $mimeType): void {
    expect(app(IsPassthroughFile::class)->handle($path, $mimeType))->toBeTrue();
})->with([
    'hidden folder' => ['.obsidian/app.json', null],
    'hidden folder inside a folder' => ['Notes/.trash/Plan.md', 'text/plain'],
    'hidden file' => ['Notes/.Plan.md', 'text/plain'],
    'only an extension' => ['.md', 'text/plain'],
    'unsupported extension' => ['Board.canvas', null],
    'no extension' => ['README', null],
    'uppercase extension' => ['photo.PNG', 'image/png'],
    'content that does not match the extension' => ['photo.png', 'text/html'],
]);
