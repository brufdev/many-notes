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
     *   content?: string|null
     * } $attributes
     */
    public function handle(VaultNode $node, array $attributes, bool $broadcastToOthers = false): VaultNode
    {
        $originalPath = new GetPathFromVaultNode()->handle($node);
        $originalLinkPath = '';
        $isNameAttributeChanged = array_key_exists('name', $attributes)
            && $attributes['name'] !== $node->name;
        $isParentIdAttributeChanged = array_key_exists('parent_id', $attributes)
            && $attributes['parent_id'] !== $node->parent_id;

        if ($isNameAttributeChanged || $isParentIdAttributeChanged) {
            /**
             * @var string $originalLinkPath
             *
             * @phpstan-ignore-next-line larastan.noUnnecessaryCollectionCall
             */
            $originalLinkPath = $node->ancestorsAndSelf()->get()->last()->full_path;
        }

        // Save node to database
        $node->update($attributes);

        // Save content to disk
        if ($node->is_file && in_array($node->extension, Note::extensions())) {
            Storage::disk('local')->put($originalPath, $attributes['content'] ?? '');
        }

        $node->refresh();

        if ($node->is_file && $node->extension === 'md' && $node->wasChanged(['content'])) {
            new ProcessVaultNodeLinks()->handle($node);
            new ProcessVaultNodeTags()->handle($node);
        }

        if ($node->wasChanged(['name', 'parent_id'])) {
            // Rename node on disk
            $path = new GetPathFromVaultNode()->handle($node);
            Storage::disk('local')->move($originalPath, $path);

            // Update all backlinks
            new UpdateVaultNodeBacklinks()->handle($node, $originalLinkPath);
        }

        if ($broadcastToOthers) {
            broadcast(new VaultNodeUpdatedEvent($node))->toOthers();
        } else {
            broadcast(new VaultNodeUpdatedEvent($node));
        }

        return $node;
    }
}
