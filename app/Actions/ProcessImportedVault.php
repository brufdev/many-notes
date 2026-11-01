<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultListUpdatedEvent;
use App\Models\User;
use App\Models\Vault;
use App\Services\VaultFile;
use App\Services\VaultFiles\Types\Note;
use finfo;
use ZipArchive;

final readonly class ProcessImportedVault
{
    private const array JUNK_FILE_NAMES = ['.ds_store', 'thumbs.db', 'desktop.ini'];

    public function handle(User $user, string $fileName, string $filePath): void
    {
        $createVaultNode = app(CreateVaultNode::class);

        $folderIds = ['' => null];
        $vaultName = app(GetVaultSafeName::class)->handle(pathinfo($fileName, PATHINFO_FILENAME), 'Imported vault');
        $vault = app(CreateVault::class)->handle($user, ['name' => $vaultName], false);

        // Create vault nodes with valid zip files and folders
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $zip = new ZipArchive();
        $zip->open($filePath);

        for ($i = 0, $zipCount = $zip->count(); $i < $zipCount; $i++) {
            $entryName = $zip->getNameIndex($i);

            if (!$entryName) {
                continue;
            }

            $entryName = str_replace('\\', '/', $entryName);
            $segments = $this->segments($entryName);

            if ($segments === null || $this->isJunk($segments)) {
                continue;
            }

            if (str_ends_with($entryName, '/')) {
                $this->folderId($vault, $segments, $folderIds);

                continue;
            }

            $pathInfo = pathinfo((string) array_pop($segments));
            $attributes = [
                'parent_id' => $this->folderId($vault, $segments, $folderIds),
                'is_file' => true,
                'name' => $pathInfo['filename'],
                'extension' => $pathInfo['extension'] ?? '',
                'content' => (string) $zip->getFromIndex($i),
            ];
            $fileMimeType = $finfo->buffer($attributes['content']) ?: '';

            if (!VaultFile::validate($attributes['extension'], $fileMimeType)) {
                continue;
            }

            if (in_array($attributes['extension'], Note::extensions())) {
                $attributes['extension'] = 'md';
            }

            $createVaultNode->handle($vault, $attributes, false, false);
        }

        $zip->close();

        app(ProcessVaultLinks::class)->handle($vault);
        app(ProcessVaultTags::class)->handle($vault);

        // Broadcast event
        broadcast(new VaultListUpdatedEvent($user))->toOthers();
    }

    /** @return list<string>|null */
    private function segments(string $entryName): ?array
    {
        // Drop empty and current folder segments
        $segments = array_values(array_filter(
            explode('/', $entryName),
            fn(string $segment): bool => $segment !== '' && $segment !== '.',
        ));

        if ($segments === [] || in_array('..', $segments, true)) {
            return null;
        }

        return $segments;
    }

    /** @param list<string> $segments */
    private function isJunk(array $segments): bool
    {
        $name = mb_strtolower((string) end($segments));

        return $segments[0] === '__MACOSX'
            || str_starts_with($name, '._')
            || in_array($name, self::JUNK_FILE_NAMES, true);
    }

    /**
     * @param list<string> $segments
     * @param array<string, int|null> $folderIds
     */
    private function folderId(Vault $vault, array $segments, array &$folderIds): ?int
    {
        $path = '';

        foreach ($segments as $segment) {
            $parentPath = $path;
            $path = $path === '' ? $segment : "{$path}/{$segment}";

            if (array_key_exists($path, $folderIds)) {
                continue;
            }

            // Create missing folder
            $folder = app(CreateVaultNode::class)->handle($vault, [
                'parent_id' => $folderIds[$parentPath],
                'is_file' => false,
                'name' => $segment,
            ], false, false);
            $folderIds[$path] = $folder->id;
        }

        return $folderIds[$path];
    }
}
