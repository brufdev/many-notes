<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

final readonly class ExportVault
{
    public function handle(Vault $vault): string
    {
        $disk = Storage::disk('local');
        $vaultPath = app(GetPathFromVault::class)->handle($vault);
        $directories = $disk->allDirectories($vaultPath);
        $files = $disk->allFiles($vaultPath);

        if ($directories === [] && $files === []) {
            throw new Exception(__('Your vault is empty'), 422);
        }

        $zip = new ZipArchive();
        $relativePath = 'public/' . Str::random(16) . '.zip';
        $path = $disk->path($relativePath);
        $disk->put($relativePath, '');

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception(__('Something went wrong'), 500);
        }

        foreach ($directories as $directory) {
            $zip->addEmptyDir(Str::chopStart($directory, $vaultPath));
        }

        foreach ($files as $file) {
            $zip->addFile($disk->path($file), Str::chopStart($file, $vaultPath));
        }

        $zip->close();

        return $path;
    }
}
