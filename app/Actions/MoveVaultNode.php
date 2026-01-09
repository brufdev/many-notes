<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultNodeMovedEvent;
use App\Models\VaultNode;

final readonly class MoveVaultNode
{
    /**
     * @param array{
     *   parent_id: int|null,
     * } $attributes
     */
    public function handle(VaultNode $node, array $attributes): VaultNode
    {
        $oldParentId = $node->parent_id;
        $updatedNode = app(UpdateVaultNode::class)->handle($node, $attributes, false);

        broadcast(new VaultNodeMovedEvent($updatedNode, $oldParentId))->toOthers();

        return $node;
    }
}
