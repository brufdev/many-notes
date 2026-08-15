<?php

declare(strict_types=1);

namespace App\Events;

use App\Actions\GetVaultTags;
use App\Models\Vault;
use App\ViewModels\VaultTagViewModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Collection as SupportCollection;

final class VaultTagListUpdatedEvent implements ShouldBroadcastNow
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

    /** @return array<string, SupportCollection<int, VaultTagViewModel>> */
    public function broadcastWith(): array
    {
        $tags = app(GetVaultTags::class)->handle($this->vault);

        return [
            'data' => $tags->map(VaultTagViewModel::fromModel(...)),
        ];
    }
}
