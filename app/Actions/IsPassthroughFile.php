<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\VaultFile;

final readonly class IsPassthroughFile
{
    public function handle(string $path, ?string $mimeType = null): bool
    {
        $segments = explode('/', $path);

        if (array_any($segments, fn(string $segment): bool => str_starts_with($segment, '.'))) {
            return true;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (!in_array($extension, VaultFile::extensions(), true)) {
            return true;
        }

        return $mimeType !== null && !VaultFile::validate($extension, $mimeType);
    }
}
