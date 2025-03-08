<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Vault;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

final readonly class ProcessDiskVault
{
    public function handle(User $user, string $path): void
    {
        $disk = Storage::build([
            'driver' => 'local',
            'root' => '/',
        ]);

        if (!$disk->exists($path) || !is_dir($path)) {
            return;
        }

        $vault = new CreateVault()->handle($user, [
            'name' => basename($path),
        ]);

        $this->processDirectory($disk, $vault, $path);

        new ProcessVaultLinks()->handle($vault);
        new ProcessVaultTags()->handle($vault);
    }

    private function processDirectory(
        Filesystem $disk,
        Vault $vault,
        string $path,
        ?int $parentId = null,
    ): void {
        $createVaultNode = new CreateVaultNode();
        $directories = $disk->directories($path);
        $files = $disk->files($path);
        $attributes = [
            'parent_id' => $parentId,
            'is_file' => false,
            'extension' => null,
            'content' => null,
        ];

        /** @var string $directory */
        foreach ($directories as $directory) {
            $attributes['name'] = pathinfo($directory, PATHINFO_BASENAME);
            $node = $createVaultNode->handle($vault, $attributes);
            $this->processDirectory($disk, $vault, "/$directory", $node->id);
        }

        /** @var string $file */
        foreach ($files as $file) {
            $pathInfo = pathinfo($file);
            $attributes['is_file'] = true;
            $attributes['name'] = $pathInfo['filename'];
            $attributes['extension'] = $pathInfo['extension'] ?? 'md';
            $attributes['content'] = (string) file_get_contents("/$file");
            $createVaultNode->handle($vault, $attributes);
        }
    }
}
