<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\VaultNode;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

final class VaultNodeUpdatedEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    /**
     * Create a new event instance.
     */
    public function __construct(
        private VaultNode $node
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
            new PresenceChannel('VaultNode.' . $this->node->id),
        ];
    }
}
