<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final readonly class MovePath
{
    public function handle(string $from, string $to): void
    {
        $disk = Storage::disk('local');

        if (!$disk->exists($from)) {
            return;
        }

        if (!$disk->move($from, $to)) {
            throw ValidationException::withMessages([
                'name' => 'The name has already been taken.',
            ]);
        }
    }
}
