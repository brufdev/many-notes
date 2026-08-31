<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class VaultNodeName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        if (preg_match('/^[. ]/u', $value) === 1) {
            $fail('Cannot start with a dot or space.');

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
        preg_match_all('/[^\w\s.,;\-&%#\[\]()=]/u', $value, $matches);

        // Only return the first three unique disallowed characters, so the message stays short
        $characters = array_slice(array_values(array_unique($matches[0])), 0, 3);

        return array_map($this->describe(...), $characters);
    }

    private function describe(string $character): string
    {
        if (preg_match('/^[\p{C}\p{Z}]$/u', $character) !== 1) {
            return $character;
        }

        // Cover zero width space case
        return sprintf('U+%04X', mb_ord($character));
    }
}
