<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class VaultSafeName implements ValidationRule
{
    public const string DISALLOWED_CHARACTERS_PATTERN = '/[#^\[\]|]/u';

    public const string INVISIBLE_NAME_PATTERN = '/^[\p{Cc}\p{Cf}\p{Z}\x{115F}\x{1160}\x{2800}\x{3164}\x{FFA0}]+$/u';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        if (preg_match(self::INVISIBLE_NAME_PATTERN, $value) === 1) {
            $fail('Cannot be only invisible characters.');

            return;
        }

        if (str_starts_with($value, '.')) {
            $fail('Cannot start with a dot.');

            return;
        }

        $disallowed = $this->disallowedCharacters($value);

        if ($disallowed !== []) {
            $fail('Cannot contain ' . implode(' ', $disallowed));
        }
    }

    /** @return list<string> */
    private function disallowedCharacters(string $value): array
    {
        preg_match_all(self::DISALLOWED_CHARACTERS_PATTERN, $value, $matches);

        // Only return the first three unique disallowed characters, so the message stays short
        return array_slice(array_values(array_unique($matches[0])), 0, 3);
    }
}
