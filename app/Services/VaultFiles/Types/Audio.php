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
        return ['mp3', 'flac', 'm4a', 'aac', 'wav', 'ogg', 'opus'];
    }

    private static function mimeTypesList(): array
    {
        return [
            'audio/mpeg',   // mp3
            'audio/flac',   // flac
            'audio/x-flac', // flac
            'audio/mp4',    // m4a
            'audio/x-m4a',  // m4a
            'audio/aac',    // aac
            'audio/x-aac',  // aac
            'audio/wav',    // wav
            'audio/x-wav',  // wav
            'audio/ogg',    // ogg, opus
            'audio/opus',   // opus
        ];
    }
}
