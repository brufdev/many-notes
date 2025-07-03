<?php

declare(strict_types=1);

namespace App\Services\VaultFiles\Types;

use App\Services\VaultFiles\Contracts\VaultFileType;
use App\Services\VaultFiles\Traits\HasVaultFileBehavior;

final class Image implements VaultFileType
{
    use HasVaultFileBehavior;

    private static function extensionsList(): array
    {
        return ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    }

    private static function mimeTypesList(): array
    {
        return ['image/'];
    }
}
