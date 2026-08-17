<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Number;

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
        return Number::fileSize(self::bytes());
    }
}
