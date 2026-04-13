<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\VaultNode;
use App\ViewModels\VaultNodeViewModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

final class VaultNodeCreatedEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(
        private VaultNode $node,
    ) {
        //
    }

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('Vault.' . $this->node->vault_id),
        ];
    }

    /** @return array<string, VaultNodeViewModel> */
    public function broadcastWith(): array
    {
        return [
            'data' => VaultNodeViewModel::fromModel($this->node),
        ];
    }
}
