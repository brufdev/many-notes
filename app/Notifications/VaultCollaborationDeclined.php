<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use App\ViewModels\VaultCollaborationDeclinedViewModel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

final class VaultCollaborationDeclined extends Notification
{
    public function __construct(
        private readonly User $user,
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
            'user_id' => $this->user->id,
        ];
    }

    public function toBroadcast(User $notifiable): BroadcastMessage
    {
        $viewModel = new VaultCollaborationDeclinedViewModel(
            $this->id,
            class_basename($this),
            $this->user,
        );

        return new BroadcastMessage([
            'data' => $viewModel->toArray(),
        ]);
    }
}
