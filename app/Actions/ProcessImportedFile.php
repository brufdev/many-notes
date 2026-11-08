<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;
use App\Models\VaultNode;
use App\Services\VaultFiles\Types\Note;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

final readonly class ProcessImportedFile
{
    public function handle(
        Vault $vault,
        ?VaultNode $parent,
        string $fileName,
        string $filePath,
        string $mimeType,
    ): VaultNode {
        $createVaultNode = app(CreateVaultNode::class);

        $pathInfo = pathinfo($fileName);
        $attributes = [
            'parent_id' => $parent?->id,
            'is_file' => true,
            'name' => $pathInfo['filename'],
            'extension' => $pathInfo['extension'] ?? null,
            'content' => null,
            'is_supported' => app(IsSupportedFile::class)->handle($fileName, $mimeType),
        ];

        if ($attributes['is_supported'] && in_array($attributes['extension'], Note::extensions())) {
            $attributes['extension'] = 'md';
            $attributes['content'] = (string) file_get_contents($filePath);
        }

        $node = $createVaultNode->handle($vault, $attributes);

        if ($attributes['content'] === null) {
            $relativePath = app(GetPathFromVaultNode::class)->handle($node);
            $pathInfo = pathinfo($relativePath);
            $savePath = $pathInfo['dirname'] ?? '';
            $saveName = $pathInfo['basename'];
            Storage::putFileAs($savePath, new File($filePath), $saveName);
        }

        return $node;
    }
}
