<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\VaultNode;
use App\ViewModels\VaultTreeNodeViewModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

final class VaultNodeCreatedEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    /**
     * Create a new event instance.
     */
    public function __construct(
        private VaultNode $node,
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
     * @return array<string, array<string, mixed>>
     */
    public function broadcastWith(): array
    {
        return [
            'node' => VaultTreeNodeViewModel::fromModel($this->node)->toArray(),
        ];
    }
}
