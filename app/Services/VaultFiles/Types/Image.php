<?php

declare(strict_types=1);

namespace App\Services\VaultFiles\Types;

use App\Services\VaultFiles\Contracts\VaultFileType;
use App\Services\VaultFiles\Traits\HasVaultFileBehavior;

final class Image implements VaultFileType
{
    use HasVaultFileBehavior;

    private static function responseMimeTypes(): array
    {
        return [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
        ];
    }

    private static function uploadMimeTypes(): array
    {
        return [
            'image/jpeg', // jpg, jpeg
            'image/png',  // png
            'image/gif',  // gif
            'image/webp', // webp
            'image/avif', // avif
        ];
    }
}
