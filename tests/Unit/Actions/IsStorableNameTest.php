<?php

declare(strict_types=1);

use App\Actions\IsStorableName;

it('accepts names that can be stored', function (string $name): void {
    expect(app(IsStorableName::class)->handle($name))->toBeTrue();
})->with([
    'note' => ['Plan.md'],
    '255 bytes' => [str_repeat('a', 252) . '.md'],
    '255 bytes of multibyte characters' => [str_repeat('漢', 85)],
    'format characters' => ["\u{1F468}\u{200D}\u{1F4BB} Work"],
]);

it('rejects names that cannot be stored', function (string $name): void {
    expect(app(IsStorableName::class)->handle($name))->toBeFalse();
})->with([
    'empty' => [''],
    '256 bytes' => [str_repeat('a', 253) . '.md'],
    '258 bytes of multibyte characters' => [str_repeat('漢', 86)],
    'tab' => ["Plan\t.md"],
    'line feed' => ["Plan\n.md"],
    'null byte' => ["Plan\0.md"],
    'delete character' => ["Plan\x7F.md"],
    'invalid UTF-8' => ["Plan\xFF.md"],
]);
