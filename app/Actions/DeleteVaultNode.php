<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultNodeDeletedEvent;
use App\Events\VaultTemplateListUpdatedEvent;
use App\Exceptions\InvalidVaultPath;
use App\Models\Scopes\SupportedVaultNodeScope;
use App\Models\VaultNode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteVaultNode
{
    /** @return array<int> */
    public function handle(VaultNode $node, bool $deleteFromDisk = true): array
    {
        $wasInTemplatesFolder = $node->isInTemplatesFolder();
        $deletedNodeIds = DB::transaction(fn(): array => $this->deleteFromDatabase($node));

        if ($deleteFromDisk) {
            $this->deleteFromDisk($node);
        }

        // Broadcast events
        broadcast(new VaultNodeDeletedEvent($node, $deletedNodeIds))->toOthers();

        if ($wasInTemplatesFolder) {
            broadcast(new VaultTemplateListUpdatedEvent($node->vault));
        }

        return $deletedNodeIds;
    }

    /** @return array<int> */
    private function deleteFromDatabase(VaultNode $node): array
    {
        $deletedNodeIds = [$node->id];

        if (!$node->is_file) {
            foreach ($node->children()->withoutGlobalScope(SupportedVaultNodeScope::class)->get() as $child) {
                $deletedNodeIds = [
                    ...$deletedNodeIds,
                    ...$this->deleteFromDatabase($child),
                ];
            }
        }

        $node->links()->detach();
        $node->backlinks()->detach();
        $node->tags()->detach();
        $node->delete();

        return $deletedNodeIds;
    }

    private function deleteFromDisk(VaultNode $node): void
    {
        try {
            $nodePath = app(GetPathFromVaultNode::class)->handle($node);
        } catch (InvalidVaultPath) {
            return;
        }

        if (!Storage::disk('local')->exists($nodePath)) {
            return;
        }

        if ($node->is_file) {
            Storage::disk('local')->delete($nodePath);
        } else {
            Storage::disk('local')->deleteDirectory($nodePath);
        }
    }
}
