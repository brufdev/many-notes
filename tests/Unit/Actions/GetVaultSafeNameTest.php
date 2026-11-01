<?php

declare(strict_types=1);

use App\Actions\GetVaultSafeName;
use App\Rules\FilesystemSafeName;
use App\Rules\VaultSafeName;

it('keeps valid names', function (string $name): void {
    expect(app(GetVaultSafeName::class)->handle($name, 'Fallback'))->toBe($name);
})->with([
    'My Vault',
    "Tom's notes",
    'C++ (2026)',
    'Café ☕',
    'v1.2',
    'CONSOLE',
]);

it('sanitizes invalid names', function (string $name, string $expected): void {
    expect(app(GetVaultSafeName::class)->handle($name, 'Fallback'))->toBe($expected);
})->with([
    'leading dot' => ['.backup', 'backup'],
    'leading dots and spaces' => [' . .backup', 'backup'],
    'trailing dots and spaces' => ['backup. .', 'backup'],
    'filesystem characters' => ['a/b\\c<d>e:f"g|h?i*j', 'a-b-c-d-e-f-g-h-i-j'],
    'link characters' => ['Ideas #1 ^a [b]', 'Ideas -1 -a -b-'],
    'control characters' => ["a\tb\nc\0d", 'a-b-c-d'],
    'invalid utf-8' => ["a\xFFb", 'a-b'],
    'reserved name' => ['CON', 'CON_'],
    'reserved name in lowercase' => ['nul', 'nul_'],
    'reserved name with a dot' => ['aux.backup', 'aux_.backup'],
    'reserved name with a superscript digit' => ['COM¹', 'COM¹_'],
    'reserved name after trimming' => ['PRN .', 'PRN_'],
]);

it('returns the fallback when nothing valid is left', function (string $name): void {
    expect(app(GetVaultSafeName::class)->handle($name, 'Fallback'))->toBe('Fallback');
})->with([
    'empty' => '',
    'space' => ' ',
    'current directory' => '.',
    'parent directory' => '..',
    'dots and spaces' => '. . .',
    'only invisible characters' => "\u{202E}",
]);

it('limits names to 255 bytes without splitting characters', function (): void {
    $name = app(GetVaultSafeName::class)->handle(str_repeat('é', 200), 'Fallback');

    expect(mb_strlen($name, '8bit'))->toBe(254);
    expect(mb_check_encoding($name, 'UTF-8'))->toBeTrue();
});

it('trims trailing dots and spaces left by the byte limit', function (): void {
    $name = app(GetVaultSafeName::class)->handle(str_repeat('a', 253) . ' .b', 'Fallback');

    expect($name)->toBe(str_repeat('a', 253));
});

it('returns names that pass the vault name rules', function (string $name): void {
    $sanitized = app(GetVaultSafeName::class)->handle($name, 'Fallback');
    $errors = [];
    $fail = function (string $error) use (&$errors): void {
        $errors[] = $error;
    };

    app(FilesystemSafeName::class)->validate('name', $sanitized, $fail);
    app(VaultSafeName::class)->validate('name', $sanitized, $fail);

    expect($errors)->toBe([]);
})->with([
    '..',
    '.obsidian',
    'CON.backup',
    'a/b\\c:d*e?f"g<h>i|j',
    '#tag ^block [[link]]',
    "\u{202E}",
    "\u{200B}.hidden",
    "tab\there",
    str_repeat('é', 200),
    str_repeat('a', 254) . '. ',
]);
