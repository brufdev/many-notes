<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\VaultNode;
use Illuminate\Support\Facades\Storage;

final readonly class UpdateVaultNode
{
    /**
     * @param array{
     *  parent_id?: int|null,
     *  is_file: bool,
     *  name: string,
     *  extension?: string|null,
     *  content?: string|null
     * } $attributes
     */
    public function handle(VaultNode $node, array $attributes): void
    {
        $relativeOriginalPath = new GetPathFromVaultNode()->handle($node);
        $node->update($attributes);

        if (!$node->wasChanged('name')) {
            return;
        }

        $relativePath = new GetPathFromVaultNode()->handle($node);
        Storage::disk('local')->move(
            $relativeOriginalPath,
            $relativePath,
        );
    }
}
