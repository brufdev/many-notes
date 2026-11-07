<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\Storage;

final readonly class GetAvailableFileName
{
    public function handle(string $directory, string $fileName): string
    {
        if (!Storage::disk('local')->exists($directory . $fileName)) {
            return $fileName;
        }

        $pathInfo = pathinfo($fileName);
        $name = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';

        // Keep dotfiles hidden
        if ($name === '') {
            $name = $fileName;
            $extension = '';
        }

        $getSuffixedName = app(GetSuffixedName::class);
        $suffixedFileName = fn(int $number): string => $getSuffixedName->handle($name, $number, $extension)
            . ($extension === '' ? '' : ".{$extension}");
        $number = 1;

        while (Storage::disk('local')->exists($directory . $suffixedFileName($number))) {
            $number++;
        }

        return $suffixedFileName($number);
    }
}
