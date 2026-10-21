<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class FilesystemSafeName implements ValidationRule
{
    public const string DISALLOWED_CHARACTERS_PATTERN = '/[\p{Cc}\/\\\\<>:"|?*]/u';

    public const string RESERVED_NAME_PATTERN = '/^(CON|PRN|AUX|NUL|COM[0-9¹²³]|LPT[0-9¹²³])(?=\.|$)/iu';

    public const int MAX_BYTES = 255;

    public function __construct(private ?string $extension = null)
    {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        if ($value === '.' || $value === '..') {
            $fail('Cannot be . or ..');

            return;
        }

        $disallowed = $this->disallowedCharacters($value);

        if ($disallowed !== []) {
            $fail('Cannot contain ' . implode(' ', $disallowed));

            return;
        }

        if (preg_match('/[. ]$/u', $value) === 1) {
            $fail('Cannot end with a dot or space.');

            return;
        }

        if (preg_match(self::RESERVED_NAME_PATTERN, $value, $matches) === 1) {
            $fail("{$matches[1]} is a reserved name.");

            return;
        }

        $fullName = $this->extension === null || $this->extension === ''
            ? $value
            : "{$value}.{$this->extension}";

        if (mb_strlen($fullName, '8bit') > self::MAX_BYTES) {
            $fail('Cannot be longer than ' . self::MAX_BYTES . ' bytes.');
        }
    }

    /** @return list<string> */
    private function disallowedCharacters(string $value): array
    {
        preg_match_all(self::DISALLOWED_CHARACTERS_PATTERN, $value, $matches);

        // Only return the first three unique disallowed characters, so the message stays short
        $characters = array_slice(array_values(array_unique($matches[0])), 0, 3);

        return array_map($this->describe(...), $characters);
    }

    private function describe(string $character): string
    {
        if (preg_match('/^\p{Cc}$/u', $character) !== 1) {
            return $character;
        }

        // Cover control characters case
        return sprintf('U+%04X', mb_ord($character));
    }
}
