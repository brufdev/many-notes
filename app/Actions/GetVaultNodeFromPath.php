<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;
use App\Models\VaultNode;

final readonly class GetVaultNodeFromPath
{
    public function handle(Vault $vault, string $path): ?VaultNode
    {
        $segments = explode('/', str_replace('%20', ' ', $path));
        $fileName = array_pop($segments);

        if (in_array($fileName, ['', '.', '..'], true)) {
            return null;
        }

        $parentId = null;

        foreach ($this->folderNames($segments) as $folderName) {
            $folder = $vault->nodes()
                ->where('parent_id', $parentId)
                ->where('is_file', false)
                ->whereRaw('LOWER(name) = LOWER(?)', [$folderName])
                ->first();

            if (is_null($folder)) {
                return null;
            }

            $parentId = $folder->id;
        }

        $pathParts = pathinfo($fileName);

        return $vault->nodes()
            ->where('parent_id', $parentId)
            ->where('is_file', true)
            ->whereRaw('LOWER(name) = LOWER(?)', [$pathParts['filename']])
            ->whereRaw('LOWER(extension) = LOWER(?)', [$pathParts['extension'] ?? 'md'])
            ->first();
    }

    /**
     * @param list<string> $segments
     * @return list<string>
     */
    private function folderNames(array $segments): array
    {
        $folderNames = [];

        // Resolve dot segments, stopping at the vault root
        foreach ($segments as $segment) {
            if ($segment === '..') {
                array_pop($folderNames);
            } elseif ($segment !== '' && $segment !== '.') {
                $folderNames[] = $segment;
            }
        }

        return $folderNames;
    }
}
