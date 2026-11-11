<?php

declare(strict_types=1);

namespace App\Actions;

final readonly class IsSafePathSegment
{
    public function handle(string $segment): bool
    {
        return !in_array($segment, ['', '.', '..'], true) && strpbrk($segment, '/\\') === false;
    }
}
