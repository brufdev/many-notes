<?php

declare(strict_types=1);

namespace App\Services\VaultFiles;

final class File
{
    /**
     * Get the extensions for the files.
     *
     * @param list<string> $extensions
     * @return list<string>
     */
    public static function extensionsWithDots(array $extensions): array
    {
        return array_map(fn (string $value): string => '.' . $value, $extensions);
    }
}
