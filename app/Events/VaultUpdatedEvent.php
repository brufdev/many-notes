<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Vault;
use App\ViewModels\VaultUpdateViewModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

final class VaultUpdatedEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(
        private Vault $vault,
    ) {
        //
    }

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('Vault.' . $this->vault->id),
        ];
    }

    /** @return array<string, array<string, mixed>> */
    public function broadcastWith(): array
    {
        return [
            'data' => VaultUpdateViewModel::fromModel($this->vault)->toArray(),
        ];
    }
}
