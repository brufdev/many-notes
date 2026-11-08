<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultListUpdatedEvent;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use App\Services\VaultFiles\Types\Note;
use finfo;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

final readonly class ProcessImportedVault
{
    private const array JUNK_FILE_NAMES = ['.ds_store', 'thumbs.db', 'desktop.ini'];

    public function handle(User $user, string $fileName, string $filePath): void
    {
        $createVaultNode = app(CreateVaultNode::class);
        $isHiddenPath = app(IsHiddenPath::class);
        $isSupportedFile = app(IsSupportedFile::class);

        $folders = ['' => null];
        $vaultName = app(GetVaultSafeName::class)->handle(pathinfo($fileName, PATHINFO_FILENAME), 'Imported vault');
        $vault = app(CreateVault::class)->handle($user, ['name' => $vaultName], false);

        // Create vault nodes for zip files and folders, and keep hidden ones on disk only
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

            $path = implode('/', $segments);
            $entryFileName = str_ends_with($entryName, '/') ? null : array_pop($segments);
            $hiddenIndex = $this->hiddenIndex($segments);
            $parent = $this->folder($vault, array_slice($segments, 0, $hiddenIndex), $folders);
            $directory = $this->directory($vault, $parent, array_slice($segments, $hiddenIndex));

            if ($entryFileName === null) {
                Storage::disk('local')->makeDirectory($directory);

                continue;
            }

            if ($isHiddenPath->handle($path)) {
                $availableName = app(GetAvailableFileName::class)->handle($directory, $entryFileName);
                $this->saveStream($zip, $i, $directory . $availableName);

                continue;
            }

            $pathInfo = pathinfo($entryFileName);
            $attributes = [
                'parent_id' => $parent?->id,
                'is_file' => true,
                'name' => $pathInfo['filename'],
                'extension' => $pathInfo['extension'] ?? null,
            ];

            if (!$isSupportedFile->handle($entryFileName)) {
                $node = $createVaultNode->handle($vault, [...$attributes, 'is_supported' => false], false, false);
                $this->saveStream($zip, $i, app(GetPathFromVaultNode::class)->handle($node));

                continue;
            }

            $attributes['content'] = (string) $zip->getFromIndex($i);
            $attributes['is_supported'] = $isSupportedFile->handle(
                $entryFileName,
                $finfo->buffer($attributes['content']) ?: '',
            );

            if ($attributes['is_supported'] && in_array($attributes['extension'], Note::extensions())) {
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
        $isStorableName = app(IsStorableName::class);

        if (
            $segments === []
            || in_array('..', $segments, true)
            || !array_all($segments, fn(string $segment): bool => $isStorableName->handle($segment))
        ) {
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

    /** @param list<string> $segments */
    private function hiddenIndex(array $segments): int
    {
        foreach ($segments as $index => $segment) {
            if (str_starts_with($segment, '.')) {
                return $index;
            }
        }

        return count($segments);
    }

    /**
     * @param list<string> $segments
     * @param array<string, VaultNode|null> $folders
     */
    private function folder(Vault $vault, array $segments, array &$folders): ?VaultNode
    {
        $path = '';

        foreach ($segments as $segment) {
            $parentPath = $path;
            $path = $path === '' ? $segment : "{$path}/{$segment}";

            if (array_key_exists($path, $folders)) {
                continue;
            }

            // Create missing folder
            $folders[$path] = app(CreateVaultNode::class)->handle($vault, [
                'parent_id' => $folders[$parentPath]?->id,
                'is_file' => false,
                'name' => $segment,
            ], false, false);
        }

        return $folders[$path];
    }

    /** @param list<string> $hiddenSegments */
    private function directory(Vault $vault, ?VaultNode $parent, array $hiddenSegments): string
    {
        $directory = app(GetPathFromVaultFolder::class)->handle($vault, $parent);

        return $directory . implode('', array_map(fn(string $segment): string => "{$segment}/", $hiddenSegments));
    }

    private function saveStream(ZipArchive $zip, int $index, string $path): void
    {
        $stream = $zip->getStreamIndex($index);

        if ($stream === false) {
            return;
        }

        Storage::disk('local')->writeStream($path, $stream);
        fclose($stream);
    }
}
