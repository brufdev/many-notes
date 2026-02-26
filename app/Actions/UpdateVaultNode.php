<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultNodeUpdatedEvent;
use App\Models\VaultNode;
use App\Services\VaultFiles\Types\Note;
use Illuminate\Support\Facades\Storage;

final readonly class UpdateVaultNode
{
    /**
     * @param array{
     *   parent_id?: int|null,
     *   name?: string,
     *   content?: string|null,
     * } $attributes
     */
    public function handle(VaultNode $node, array $attributes): VaultNode
    {
        $originalPath = app(GetPathFromVaultNode::class)->handle($node);
        $originalLinkPath = '';

        $isNameAttributeChanged = array_key_exists('name', $attributes)
            && $attributes['name'] !== $node->name;
        $isParentIdAttributeChanged = array_key_exists('parent_id', $attributes)
            && $attributes['parent_id'] !== $node->parent_id;

        if ($isNameAttributeChanged || $isParentIdAttributeChanged) {
            $originalLinkPath = $node->fullPath();
        }

        // Save node to database
        $node->update($attributes);

        // Save content to disk
        if ($node->is_file && in_array($node->extension, Note::extensions())) {
            Storage::disk('local')->put($originalPath, $attributes['content'] ?? '');
        }

        $node->refresh();

        if ($node->is_file && $node->extension === 'md' && $node->wasChanged(['content'])) {
            app(ProcessVaultNodeLinks::class)->handle($node);
            app(ProcessVaultNodeTags::class)->handle($node);
        }

        if ($node->wasChanged(['name', 'parent_id'])) {
            // Rename node on disk
            $path = app(GetPathFromVaultNode::class)->handle($node);
            Storage::disk('local')->move($originalPath, $path);

            // Update all backlinks
            app(UpdateVaultNodeBacklinks::class)->handle($node, $originalLinkPath);

            // Broadcast events
            broadcast(new VaultNodeUpdatedEvent($node))->toOthers();
        }

        return $node;
    }
}
