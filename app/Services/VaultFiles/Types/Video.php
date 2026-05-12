<?php

declare(strict_types=1);

namespace App\Services\VaultFiles\Types;

use App\Services\VaultFiles\Contracts\VaultFileType;
use App\Services\VaultFiles\Traits\HasVaultFileBehavior;

final class Video implements VaultFileType
{
    use HasVaultFileBehavior;

    private static function extensionsList(): array
    {
        return ['mp4', 'm4v', 'mov', 'webm', 'mkv'];
    }

    private static function mimeTypesList(): array
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
