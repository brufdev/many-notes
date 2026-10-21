<?php

declare(strict_types=1);

use App\Rules\FilesystemSafeName;

function validateFilesystemSafeName(mixed $value, ?string $extension = null): ?string
{
    $message = null;

    new FilesystemSafeName($extension)->validate('name', $value, function (string $error) use (&$message): void {
        $message = $error;
    });

    return $message;
}

it('accepts valid names', function (string $name): void {
    expect(validateFilesystemSafeName($name))->toBeNull();
})->with([
    'My Vault',
    "Tom's notes",
    'C++ (2026)',
    'Café ☕',
    '#tag [link] ^block',
    '.obsidian',
    'v1.2',
    'CONSOLE',
    'Configs',
    'COM10',
]);

it('ignores non-string values', function (): void {
    expect(validateFilesystemSafeName(null))->toBeNull();
});

it('rejects current and parent directory names', function (string $name): void {
    expect(validateFilesystemSafeName($name))->toBe('Cannot be . or ..');
})->with(['.', '..']);

it('rejects disallowed characters', function (string $name, string $message): void {
    expect(validateFilesystemSafeName($name))->toBe($message);
})->with([
    'slash' => ['a/b', 'Cannot contain /'],
    'backslash' => ['a\\b', 'Cannot contain \\'],
    'windows characters' => ['a<b>c', 'Cannot contain < >'],
    'first three unique' => ['a:b:c"d|e?f*', 'Cannot contain : " |'],
    'tab' => ["a\tb", 'Cannot contain U+0009'],
    'null byte' => ["a\0b", 'Cannot contain U+0000'],
]);

it('rejects names ending with a dot or space', function (string $name): void {
    expect(validateFilesystemSafeName($name))->toBe('Cannot end with a dot or space.');
})->with(['notes.', 'notes ', '...']);

it('rejects windows reserved names', function (string $name, string $message): void {
    expect(validateFilesystemSafeName($name))->toBe($message);
})->with([
    'uppercase' => ['CON', 'CON is a reserved name.'],
    'lowercase' => ['aux', 'aux is a reserved name.'],
    'with a dot' => ['nul.backup', 'nul is a reserved name.'],
    'with a digit' => ['COM1', 'COM1 is a reserved name.'],
    'with a zero' => ['LPT0', 'LPT0 is a reserved name.'],
    'with a superscript digit' => ['COM¹', 'COM¹ is a reserved name.'],
]);

it('rejects windows reserved names of files', function (): void {
    expect(validateFilesystemSafeName('prn', 'md'))->toBe('prn is a reserved name.');
});

it('accepts names up to 255 bytes', function (): void {
    expect(validateFilesystemSafeName(str_repeat('a', 255)))->toBeNull();
    expect(validateFilesystemSafeName(str_repeat('a', 252), 'md'))->toBeNull();
});

it('rejects names longer than 255 bytes', function (): void {
    expect(validateFilesystemSafeName(str_repeat('a', 256)))->toBe('Cannot be longer than 255 bytes.');
});

it('counts the extension in the byte limit', function (): void {
    expect(validateFilesystemSafeName(str_repeat('a', 253), 'md'))->toBe('Cannot be longer than 255 bytes.');
});

it('counts bytes instead of characters', function (): void {
    // 128 characters, 256 bytes
    expect(validateFilesystemSafeName(str_repeat('é', 128)))->toBe('Cannot be longer than 255 bytes.');
});
