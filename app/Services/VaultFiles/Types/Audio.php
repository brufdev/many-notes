<?php

declare(strict_types=1);

namespace App\Services\VaultFiles\Types;

use App\Services\VaultFiles\Contracts\VaultFileType;
use App\Services\VaultFiles\Traits\HasVaultFileBehavior;

final class Audio implements VaultFileType
{
    use HasVaultFileBehavior;

    private static function responseMimeTypes(): array
    {
        return [
            'mp3' => 'audio/mpeg',
            'flac' => 'audio/flac',
            'm4a' => 'audio/mp4',
            'aac' => 'audio/aac',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'opus' => 'audio/ogg',
        ];
    }

    private static function uploadMimeTypes(): array
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
