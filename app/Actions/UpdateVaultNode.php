<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\VaultNode;
use Illuminate\Support\Facades\Storage;

final readonly class UpdateVaultNode
{
    /**
     * @param array{
     *   parent_id?: int|null,
     *   is_file?: bool,
     *   name?: string,
     *   extension?: string|null,
     *   content?: string|null
     * } $attributes
     */
    public function handle(VaultNode $node, array $attributes): void
    {
        $originalPath = new GetPathFromVaultNode()->handle($node);

        // Save node to database
        $node->update($attributes);

        // Save node to disk
        if ($node->is_file) {
            Storage::disk('local')->put($originalPath, $attributes['content'] ?? '');
        }

        // Rename node on disk
        if ($node->wasChanged('name')) {
            $path = new GetPathFromVaultNode()->handle($node);
            Storage::disk('local')->move($originalPath, $path);
        }

        // Move node on disk
        if ($node->wasChanged('parent_id')) {
            $path = new GetPathFromVaultNode()->handle($node);
            Storage::disk('local')->move($originalPath, $path);
        }
    }
}
