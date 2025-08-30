<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\VaultFiles\Contracts\VaultFileType;
use App\Services\VaultFiles\Types\Audio;
use App\Services\VaultFiles\Types\Image;
use App\Services\VaultFiles\Types\Note;
use App\Services\VaultFiles\Types\Pdf;
use App\Services\VaultFiles\Types\Video;

final class VaultFile
{
    /** @var list<class-string<VaultFileType>> */
    private static array $fileTypes = [
        Audio::class,
        Image::class,
        Note::class,
        Pdf::class,
        Video::class,
    ];

    /**
     * Get the extensions for the supported vault files, with or without the dot.
     *
     * @return list<string>
     */
    public static function extensions(bool $withDots = false): array
    {
        $all = [];

        foreach (self::$fileTypes as $type) {
            foreach ($type::extensions($withDots) as $ext) {
                $all[] = $ext;
            }
        }

        return $all;
    }

    /**
     * Validate if the extension and mimetype correspond to a supported vault file.
     */
    public static function validate(string $extension, string $mimeType): bool
    {
        return array_any(
            self::$fileTypes,
            fn(string $typeClass): bool => $typeClass::validate($extension, $mimeType),
        );
    }
}
