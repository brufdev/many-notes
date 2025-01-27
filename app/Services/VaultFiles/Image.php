<?php

declare(strict_types=1);

namespace App\Services\VaultFiles;

final class Image
{
    /** @var list<string> */
    private static array $extensions = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
    ];

    /**
     * Get the extensions for the image files.
     *
     * @return list<string>
     */
    public static function extensions(bool $withDots = false): array
    {
        return $withDots ? File::extensionsWithDots(self::$extensions) : self::$extensions;
    }
}
