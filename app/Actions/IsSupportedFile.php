<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\VaultFile;

final readonly class IsSupportedFile
{
    public function handle(string $fileName, ?string $mimeType = null): bool
    {
        $extension = mb_strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($extension, VaultFile::extensions(), true)) {
            return false;
        }

        return $mimeType === null || VaultFile::validate($extension, $mimeType);
    }
}
