<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\VaultNode;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

final class VaultNodeDeletedEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    /**
     * Create a new event instance.
     *
     * @param array<int> $nodeIds
     */
    public function __construct(
        private VaultNode $node,
        private array $nodeIds,
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
     * @return array<string, array<int>>
     */
    public function broadcastWith(): array
    {
        return [
            'nodeIds' => $this->nodeIds,
        ];
    }
}
