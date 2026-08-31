<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultNodeCreatedEvent;
use App\Events\VaultTemplateListUpdatedEvent;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Support\Facades\Storage;

final readonly class CreateVaultNode
{
    /**
     * @param array{
     *   parent_id?: int|null,
     *   is_file: bool,
     *   name: string,
     *   extension?: string|null,
     *   content?: string|null
     * } $attributes
     */
    public function handle(
        Vault $vault,
        array $attributes,
        bool $processLinksAndTags = true,
        bool $broadcast = true,
    ): VaultNode {
        $attributes['parent_id'] ??= null;
        $attributes['extension'] ??= null;
        $attributes['content'] ??= null;

        // Generate a new filename if the current one already exists
        $attributes['name'] = app(GetAvailableVaultNodeName::class)->handle(
            $vault,
            $attributes['parent_id'],
            $attributes['is_file'],
            $attributes['name'],
            $attributes['extension'],
        );

        // Save node to database
        $databaseContent = $attributes['extension'] === 'md' ? $attributes['content'] : null;
        $node = $vault->nodes()->create([
            'parent_id' => $attributes['parent_id'],
            'is_file' => $attributes['is_file'],
            'name' => $attributes['name'],
            'extension' => $attributes['extension'],
            'content' => $databaseContent,
        ]);

        // Save node to disk
        $nodePath = app(GetPathFromVaultNode::class)->handle($node);

        if ($node->is_file) {
            if ($node->extension === 'md' && $processLinksAndTags) {
                app(ProcessVaultNodeLinks::class)->handle($node);
                app(ProcessVaultNodeTags::class)->handle($node);
            }

            Storage::disk('local')->put($nodePath, $attributes['content'] ?? '');
        } else {
            Storage::disk('local')->makeDirectory($nodePath);
        }

        // Broadcast events
        if ($broadcast) {
            broadcast(new VaultNodeCreatedEvent($node))->toOthers();

            if ($node->isTemplate()) {
                broadcast(new VaultTemplateListUpdatedEvent($vault));
            }
        }

        return $node;
    }
}
