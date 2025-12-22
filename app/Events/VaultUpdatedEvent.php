<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Vault;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

final class VaultUpdatedEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    /**
     * Create a new event instance.
     */
    public function __construct(
        private Vault $vault,
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
            new PrivateChannel('Vault.' . $this->vault->id),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function broadcastWith(): array
    {
        return [
            'vault' => [
                'name' => $this->vault->name,
                'templates_node_id' => $this->vault->templates_node_id,
            ],
        ];
    }
}
