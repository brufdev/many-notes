<?php

declare(strict_types=1);

namespace App\Services;

use RoundingMode;

final class UploadLimit
{
    public static function bytes(): int
    {
        $uploadMax = ini_parse_quantity(ini_get('upload_max_filesize') ?: '0');
        $postMax = ini_parse_quantity(ini_get('post_max_size') ?: '0');

        return $postMax > 0 ? min($uploadMax, $postMax) : $uploadMax;
    }

    public static function kilobytes(): int
    {
        return intdiv(self::bytes(), 1024);
    }

    public static function label(): string
    {
        return self::format(self::bytes());
    }

    public static function format(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $size = (float) $bytes;
        $unit = 0;

        while ($size / 1024 > 0.9 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return number_format(round($size, 0, RoundingMode::HalfEven)) . ' ' . $units[$unit];
    }

    public static function unzippedBytes(): int
    {
        return ini_parse_quantity(config()->string('settings.import.max_unzipped_size'));
    }
}
