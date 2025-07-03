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
        return ['mp4', 'avi'];
    }

    private static function mimeTypesList(): array
    {
        return ['video/'];
    }
}
