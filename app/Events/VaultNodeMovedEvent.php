<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\VaultNode;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

final class VaultNodeMovedEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    /**
     * Create a new event instance.
     */
    public function __construct(
        private VaultNode $node,
        private ?int $oldParentId,
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('Vault.' . $this->node->vault_id),
        ];
    }

    /**
     * @return array<string, int|null>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->node->id,
            'old_parent_id' => $this->oldParentId,
            'new_parent_id' => $this->node->parent_id,
        ];
    }
}
