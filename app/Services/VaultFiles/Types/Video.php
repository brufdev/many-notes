<?php

declare(strict_types=1);

namespace App\Services\VaultFiles\Types;

use App\Services\VaultFiles\Contracts\VaultFileType;
use App\Services\VaultFiles\Traits\HasVaultFileBehavior;

final class Video implements VaultFileType
{
    use HasVaultFileBehavior;

    private static function responseMimeTypes(): array
    {
        return [
            'mp4' => 'video/mp4',
            'm4v' => 'video/mp4',
            'mov' => 'video/quicktime',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
        ];
    }

    private static function uploadMimeTypes(): array
    {
        return [
            'video/mp4',        // mp4, m4v
            'video/x-m4v',      // m4v
            'video/quicktime',  // mov
            'video/webm',       // webm
            'video/x-matroska', // mkv
        ];
    }
}
