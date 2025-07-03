<?php

declare(strict_types=1);

namespace App\Services\VaultFiles\Types;

use App\Services\VaultFiles\Contracts\VaultFileType;
use App\Services\VaultFiles\Traits\HasVaultFileBehavior;

final class Audio implements VaultFileType
{
    use HasVaultFileBehavior;

    private static function extensionsList(): array
    {
        return ['mp3', 'flac'];
    }

    private static function mimeTypesList(): array
    {
        return ['audio/'];
    }
}
