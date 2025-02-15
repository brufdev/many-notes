<?php

declare(strict_types=1);

namespace App\Services\VaultFiles;

final class Video
{
    /** @var list<string> */
    private static array $extensions = [
        'mp4',
        'avi',
    ];

    /**
     * Get the extensions for the video files.
     *
     * @return list<string>
     */
    public static function extensions(bool $withDots = false): array
    {
        return $withDots ? File::extensionsWithDots(self::$extensions) : self::$extensions;
    }
}
