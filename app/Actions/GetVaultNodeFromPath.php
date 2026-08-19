<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\VaultNode;
use Illuminate\Support\Str;

final readonly class GetVaultNodeFromPath
{
    public function handle(int $vaultId, string $path, ?int $parentId = null): ?VaultNode
    {
        $path = mb_ltrim(str_replace('%20', ' ', $path), '/');
        $pieces = explode('/', $path);

        if (count($pieces) === 1) {
            $pathParts = pathinfo($pieces[0]);

            return VaultNode::query()
                ->where('vault_id', $vaultId)
                ->where('parent_id', $parentId)
                ->where('is_file', true)
                ->whereRaw('LOWER(name) = LOWER(?)', [$pathParts['filename']])
                ->whereRaw('LOWER(extension) = LOWER(?)', [$pathParts['extension'] ?? 'md'])
                ->first();
        }

        $node = VaultNode::query()
            ->where('vault_id', $vaultId)
            ->where('parent_id', $parentId)
            ->where('is_file', false)
            ->whereRaw('LOWER(name) = LOWER(?)', [$pieces[0]])
            ->first();

        if (is_null($node)) {
            return $node;
        }

        return $this->handle($vaultId, Str::after($path, '/'), $node->id);
    }
}
