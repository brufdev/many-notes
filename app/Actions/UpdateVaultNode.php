<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultNodeUpdatedEvent;
use App\Events\VaultOpenedFileDataUpdatedEvent;
use App\Events\VaultTagListUpdatedEvent;
use App\Events\VaultTemplateListUpdatedEvent;
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
    public function handle(
        VaultNode $node,
        array $attributes,
        bool $broadcastToCurrentUser = false,
    ): VaultNode {
        $originalPath = app(GetPathFromVaultNode::class)->handle($node);
        $originalLinkPath = '';

        $isNameAttributeChanged = array_key_exists('name', $attributes)
            && $attributes['name'] !== $node->name;
        $isParentIdAttributeChanged = array_key_exists('parent_id', $attributes)
            && $attributes['parent_id'] !== $node->parent_id;
        $wasInTemplatesFolder = ($isNameAttributeChanged || $isParentIdAttributeChanged)
            && $node->isInTemplatesFolder();

        if ($isNameAttributeChanged || $isParentIdAttributeChanged) {
            $originalLinkPath = $node->fullPath();
        }

        // Save node to database
        $node->update($attributes);

        // Save content of note to disk
        if (
            $node->is_file
            && array_key_exists('content', $attributes)
            && in_array($node->extension, Note::extensions())
        ) {
            Storage::disk('local')->put($originalPath, $attributes['content'] ?? '');
        }

        $node->refresh();

        if ($node->is_file && $node->extension === 'md' && $node->wasChanged(['content'])) {
            $previousLinks = $this->getLinks($node);
            app(ProcessVaultNodeLinks::class)->handle($node);
            $newLinks = $this->getLinks($node);

            $previousTags = $this->getTags($node);
            app(ProcessVaultNodeTags::class)->handle($node);
            $newTags = $this->getTags($node);

            if ($previousLinks !== $newLinks || $previousTags !== $newTags) {
                // Broadcast events
                broadcast(new VaultOpenedFileDataUpdatedEvent($node));
            }

            if ($previousTags !== $newTags) {
                // Broadcast events
                broadcast(new VaultTagListUpdatedEvent($node->vault));
            }
        }

        if ($node->wasChanged(['name', 'parent_id'])) {
            // Rename node on disk
            $path = app(GetPathFromVaultNode::class)->handle($node);
            Storage::disk('local')->move($originalPath, $path);

            // Update all backlinks
            app(UpdateVaultNodeBacklinks::class)->handle($node, $originalLinkPath);
        }

        // Broadcast events
        $pendingBroadcast = broadcast(new VaultNodeUpdatedEvent($node));

        if (!$broadcastToCurrentUser) {
            $pendingBroadcast->toOthers();
        }

        if (
            $node->wasChanged(['name', 'parent_id'])
            && ($wasInTemplatesFolder || $node->isInTemplatesFolder())
        ) {
            // Broadcast events
            broadcast(new VaultTemplateListUpdatedEvent($node->vault));
        }

        if ($node->wasChanged(['name'])) {
            $backlinks = $node->backlinks()->get();

            // Broadcast events
            foreach ($backlinks as $backlink) {
                broadcast(new VaultOpenedFileDataUpdatedEvent($backlink))->toOthers();
            }
        }

        return $node;
    }

    /** @return array<mixed> */
    private function getLinks(VaultNode $node): array
    {
        return $node
            ->links()
            ->get()
            ->pluck('pivot.destination_id', 'pivot.position')
            ->toArray();
    }

    /** @return array<mixed> */
    private function getTags(VaultNode $node): array
    {
        return $node
            ->tags()
            ->get()
            ->pluck('pivot.tag_id', 'pivot.position')
            ->toArray();
    }
}
