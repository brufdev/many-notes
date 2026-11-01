<?php

declare(strict_types=1);

namespace App\Actions;

use App\Rules\FilesystemSafeName;

final readonly class GetSuffixedName
{
    public function handle(string $name, int $number, ?string $extension = null): string
    {
        $suffix = "-{$number}";
        $extensionBytes = $extension === null || $extension === '' ? 0 : mb_strlen(".{$extension}", '8bit');
        $maxBytes = FilesystemSafeName::MAX_BYTES - mb_strlen($suffix, '8bit') - $extensionBytes;

        // Cut the name so the suffix and extension fit
        return mb_strcut($name, 0, $maxBytes, 'UTF-8') . $suffix;
    }
}
