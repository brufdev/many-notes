<?php

declare(strict_types=1);

namespace App\Actions;

use App\Rules\FilesystemSafeName;

final readonly class IsStorableName
{
    public function handle(string $name): bool
    {
        return $name !== ''
            && mb_strlen($name, '8bit') <= FilesystemSafeName::MAX_BYTES
            && preg_match('/\p{Cc}/u', $name) === 0;
    }
}
