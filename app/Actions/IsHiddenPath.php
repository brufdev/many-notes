<?php

declare(strict_types=1);

namespace App\Actions;

final readonly class IsHiddenPath
{
    public function handle(string $path): bool
    {
        return array_any(explode('/', $path), fn(string $segment): bool => str_starts_with($segment, '.'));
    }
}
