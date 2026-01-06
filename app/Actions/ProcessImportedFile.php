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
    public function handle(Vault $vault, ?VaultNode $parent, string $fileName, string $filePath): VaultNode
    {
        $createVaultNode = app(CreateVaultNode::class);
        $getPathFromVaultNode = app(GetPathFromVaultNode::class);

        $attributes = [
            'parent_id' => $parent?->id,
            'is_file' => true,
        ];
        $pathInfo = pathinfo($fileName);
        $attributes['name'] = $pathInfo['filename'];
        $attributes['extension'] = $pathInfo['extension'] ?? '';
        $attributes['content'] = null;

        if (in_array($attributes['extension'], Note::extensions())) {
            $attributes['extension'] = 'md';
            $attributes['content'] = (string) file_get_contents($filePath);
        }

        $node = $createVaultNode->handle($vault, $attributes);

        if ($node->extension !== 'md') {
            $relativePath = $getPathFromVaultNode->handle($node);
            $pathInfo = pathinfo($relativePath);
            $savePath = $pathInfo['dirname'] ?? '';
            $saveName = $pathInfo['basename'];
            Storage::putFileAs($savePath, new File($filePath), $saveName);
        }

        return $node;
    }
}
