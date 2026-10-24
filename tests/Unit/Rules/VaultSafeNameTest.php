<?php

declare(strict_types=1);

use App\Rules\VaultSafeName;

function validateVaultSafeName(mixed $value): ?string
{
    $message = null;

    new VaultSafeName()->validate('name', $value, function (string $error) use (&$message): void {
        $message = $error;
    });

    return $message;
}

it('accepts valid names', function (string $name): void {
    expect(validateVaultSafeName($name))->toBeNull();
})->with([
    'My Vault',
    "Tom's notes",
    'C++ (2026)',
    'Café ☕',
    'v1.2',
    'emoji with joiners' => "\u{1F468}\u{200D}\u{1F469}\u{200D}\u{1F467}",
    'persian with non-joiner' => "\u{0645}\u{06CC}\u{200C}\u{062E}\u{0648}\u{0627}\u{0647}\u{0645}",
    'visible text with a zero width space' => "Plan\u{200B}",
]);

it('ignores non-string values', function (): void {
    expect(validateVaultSafeName(null))->toBeNull();
});

it('rejects names with only invisible characters', function (string $name): void {
    expect(validateVaultSafeName($name))->toBe('Cannot be only invisible characters.');
})->with([
    'zero width space' => "\u{200B}",
    'zero width spaces and spaces' => "\u{200B} \u{200B}",
    'non-breaking space' => "\u{00A0}",
    'hangul filler' => "\u{3164}",
    'braille blank' => "\u{2800}",
]);

it('rejects names starting with a dot', function (string $name): void {
    expect(validateVaultSafeName($name))->toBe('Cannot start with a dot.');
})->with(['.obsidian', '.hidden note']);

it('rejects link characters', function (string $name, string $message): void {
    expect(validateVaultSafeName($name))->toBe($message);
})->with([
    'hash' => ['#tag', 'Cannot contain #'],
    'caret' => ['a^b', 'Cannot contain ^'],
    'brackets' => ['[link]', 'Cannot contain [ ]'],
    'pipe' => ['a|b', 'Cannot contain |'],
    'first three unique' => ['#a^b[c]d|', 'Cannot contain # ^ ['],
]);
