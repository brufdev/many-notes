<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

final readonly class CreateZipFromDirectory
{
    public function handle(string $directory): ?string
    {
        $disk = Storage::disk('local');
        $directory = Str::finish($directory, '/');
        $directories = $disk->allDirectories($directory);
        $files = $disk->allFiles($directory);

        if ($directories === [] && $files === []) {
            return null;
        }

        $zip = new ZipArchive();
        $relativePath = 'public/' . Str::random(16) . '.zip';
        $path = $disk->path($relativePath);
        $disk->put($relativePath, '');

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Could not create the zip file at {$path}");
        }

        foreach ($directories as $subdirectory) {
            $zip->addEmptyDir(Str::chopStart($subdirectory, $directory));
        }

        foreach ($files as $file) {
            $zip->addFile($disk->path($file), Str::chopStart($file, $directory));
        }

        $zip->close();

        return $path;
    }
}
