<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultNodeDeletedEvent;
use App\Models\VaultNode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteVaultNode
{
    /** @return array<int> */
    public function handle(VaultNode $node, bool $deleteFromDisk = true): array
    {
        $deletedNodeIds = DB::transaction(fn(): array => $this->deleteFromDatabase($node));

        if ($deleteFromDisk) {
            $this->deleteFromDisk($node);
        }

        broadcast(new VaultNodeDeletedEvent($node, $deletedNodeIds))->toOthers();

        return $deletedNodeIds;
    }

    /** @return array<int> */
    private function deleteFromDatabase(VaultNode $node): array
    {
        $deletedNodeIds = [$node->id];

        if (!$node->is_file) {
            foreach ($node->children()->get() as $child) {
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
        $nodePath = app(GetPathFromVaultNode::class)->handle($node);

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
