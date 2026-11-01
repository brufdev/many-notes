<?php

declare(strict_types=1);

namespace App\Actions;

use App\Rules\FilesystemSafeName;
use App\Rules\VaultSafeName;

final readonly class GetVaultSafeName
{
    public function handle(string $name, string $fallback): string
    {
        $name = mb_scrub($name, 'UTF-8');

        // Replace disallowed characters
        $name = (string) preg_replace(FilesystemSafeName::DISALLOWED_CHARACTERS_PATTERN, '-', $name);
        $name = (string) preg_replace(VaultSafeName::DISALLOWED_CHARACTERS_PATTERN, '-', $name);

        $name = mb_trim($name, '. ');

        // Suffix Windows reserved names
        $name = (string) preg_replace(FilesystemSafeName::RESERVED_NAME_PATTERN, '$1_', $name);

        $name = mb_rtrim(mb_strcut($name, 0, FilesystemSafeName::MAX_BYTES, 'UTF-8'), '. ');

        if ($name === '' || preg_match(VaultSafeName::INVISIBLE_NAME_PATTERN, $name) === 1) {
            return $fallback;
        }

        return $name;
    }
}
