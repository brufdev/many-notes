<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Tag;
use App\Models\Vault;
use App\ViewModels\VaultTagViewModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

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
        /** @var Collection<int, Tag&object{total: int}> */
        $tags = $this->vault
            ->tags()
            ->select(DB::raw('tags.*, count(*) AS total'))
            ->groupBy('tags.id', 'tags.name', 'vault_nodes.vault_id')
            ->orderBy('tags.name')
            ->get();

        return [
            'data' => $tags->map(VaultTagViewModel::fromModel(...)),
        ];
    }
}
