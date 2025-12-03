<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Vault;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

final class VaultDeletedEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    /**
     * Create a new event instance.
     */
    public function __construct(
        private Vault $vault
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
}
