<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use App\Models\Vault;
use App\ViewModels\VaultCollaborationInvitationReceivedViewModel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

final class VaultCollaborationInvitationReceived extends Notification
{
    public function __construct(
        private readonly Vault $vault,
    ) {
        //
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'vault_id' => $this->vault->id,
        ];
    }

    public function toBroadcast(User $notifiable): BroadcastMessage
    {
        $viewModel = new VaultCollaborationInvitationReceivedViewModel(
            $this->id,
            class_basename($this),
            $notifiable,
            $this->vault,
        );

        return new BroadcastMessage([
            'data' => $viewModel->toArray(),
        ]);
    }
}
