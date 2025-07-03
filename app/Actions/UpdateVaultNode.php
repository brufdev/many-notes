<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\VaultNode;
use App\Services\VaultFiles\Types\Note;
use Illuminate\Support\Facades\Storage;

final readonly class UpdateVaultNode
{
    /**
     * @param array{
     *   parent_id?: int|null,
     *   name?: string,
     *   content?: string|null
     * } $attributes
     */
    public function handle(VaultNode $node, array $attributes): void
    {
        $originalPath = new GetPathFromVaultNode()->handle($node);

        // Save node to database
        $node->update($attributes);

        // Save content to disk
        if ($node->is_file && in_array($node->extension, Note::extensions())) {
            Storage::disk('local')->put($originalPath, $attributes['content'] ?? '');
        }

        // Rename node on disk
        if ($node->wasChanged(['name', 'parent_id'])) {
            $path = new GetPathFromVaultNode()->handle($node);
            Storage::disk('local')->move($originalPath, $path);
        }
    }
}
