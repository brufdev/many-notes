<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Services\VaultFile;
use App\Services\VaultFiles\Note;
use ZipArchive;

final readonly class ProcessImportedVault
{
    public function handle(string $fileName, string $filePath): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $nodeIds = ['.' => null];
        $vaultName = pathinfo($fileName, PATHINFO_FILENAME);
        $vault = new CreateVault()->handle($currentUser, [
            'name' => $vaultName,
        ]);

        // Create vault nodes with valid zip files and folders
        $zip = new ZipArchive();
        $zip->open($filePath);

        for ($i = 0, $zipCount = $zip->count(); $i < $zipCount; $i++) {
            $entryName = $zip->getNameIndex($i);

            if (!$entryName) {
                continue;
            }

            $isFile = !str_ends_with($entryName, '/');
            $flags = $isFile ? PATHINFO_FILENAME : PATHINFO_BASENAME;
            $attributes = [
                'is_file' => $isFile,
                'name' => pathinfo($entryName, $flags),
                'extension' => null,
                'content' => null,
            ];

            if (!$isFile) {
                // ZipArchive folder paths end with a / that should
                // be removed in order for pathinfo() return the correct dirname
                $entryDirName = mb_rtrim($entryName, '/');
                $entryParentDirName = pathinfo($entryDirName, PATHINFO_DIRNAME);
                $attributes['parent_id'] = $nodeIds[$entryParentDirName];
            } else {
                $pathInfo = pathinfo($entryName);
                $entryDirName = $pathInfo['dirname'];
                $attributes['extension'] = $pathInfo['extension'] ?? '';
                $attributes['parent_id'] = $nodeIds[$entryDirName];

                if (!in_array($attributes['extension'], VaultFile::extensions())) {
                    continue;
                }

                if (in_array($attributes['extension'], Note::extensions())) {
                    $attributes['extension'] = 'md';
                }

                $attributes['content'] = (string) $zip->getFromIndex($i);
            }

            $node = new CreateVaultNode()->handle($vault, $attributes);

            if (!array_key_exists($entryDirName, $nodeIds)) {
                $nodeIds[$entryDirName] = $node->id;
            }
        }

        $zip->close();

        new ProcessVaultLinks()->handle($vault);
        new ProcessVaultTags()->handle($vault);
    }
}
