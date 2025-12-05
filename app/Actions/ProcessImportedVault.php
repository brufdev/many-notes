<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultListUpdatedEvent;
use App\Models\User;
use App\Services\VaultFile;
use App\Services\VaultFiles\Types\Note;
use finfo;
use ZipArchive;

final readonly class ProcessImportedVault
{
    public function handle(User $user, string $fileName, string $filePath): void
    {
        $createVaultNode = app(CreateVaultNode::class);

        $nodeIds = ['.' => null];
        $vaultName = pathinfo($fileName, PATHINFO_FILENAME);
        $vault = app(CreateVault::class)->handle($user, [
            'name' => $vaultName,
        ]);

        // Create vault nodes with valid zip files and folders
        $finfo = new finfo(FILEINFO_MIME_TYPE);
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
                $attributes['content'] = (string) $zip->getFromIndex($i);
                $fileMimeType = $finfo->buffer($attributes['content']) ?: '';

                if (!VaultFile::validate($attributes['extension'], $fileMimeType)) {
                    continue;
                }

                if (in_array($attributes['extension'], Note::extensions())) {
                    $attributes['extension'] = 'md';
                }
            }

            $node = $createVaultNode->handle($vault, $attributes, false);

            if (!array_key_exists($entryDirName, $nodeIds)) {
                $nodeIds[$entryDirName] = $node->id;
            }
        }

        $zip->close();

        app(ProcessVaultLinks::class)->handle($vault);
        app(ProcessVaultTags::class)->handle($vault);

        broadcast(new VaultListUpdatedEvent($user))->toOthers();
    }
}
