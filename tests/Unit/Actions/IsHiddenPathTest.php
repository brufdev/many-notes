<?php

declare(strict_types=1);

use App\Actions\IsHiddenPath;

it('detects hidden paths', function (string $path): void {
    expect(app(IsHiddenPath::class)->handle($path))->toBeTrue();
})->with([
    'hidden folder' => ['.obsidian/app.json'],
    'hidden folder inside a folder' => ['Notes/.trash/Plan.md'],
    'hidden file' => ['Notes/.Plan.md'],
    'only an extension' => ['.md'],
]);

it('detects visible paths', function (string $path): void {
    expect(app(IsHiddenPath::class)->handle($path))->toBeFalse();
})->with([
    'note' => ['Notes/Plan.md'],
    'unsupported file' => ['Board.canvas'],
    'dot inside a name' => ['Notes/v1.2/Plan.md'],
]);
