<?php

declare(strict_types=1);

namespace App\Events;

use App\Actions\GetVaultPageData;
use App\Models\Vault;
use App\ViewModels\VaultEditorTemplateViewModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Collection as SupportCollection;

final class VaultTemplateListUpdatedEvent implements ShouldBroadcastNow
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

    /** @return array<string, SupportCollection<int, VaultEditorTemplateViewModel>|null> */
    public function broadcastWith(): array
    {
        $templateNodes = app(GetVaultPageData::class)->handle($this->vault)['templateNodes']();

        return [
            'data' => $templateNodes?->map(VaultEditorTemplateViewModel::fromModel(...)),
        ];
    }
}
