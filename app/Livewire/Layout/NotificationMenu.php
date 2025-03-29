<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use App\Models\User;
use App\Models\Vault;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Attributes\On;
use Livewire\Component;

#[On('notifications-refresh')]
final class NotificationMenu extends Component
{
    public function render(): Factory|View
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $notifications = [];

        foreach ($currentUser->notifications as $notification) {
            $type = class_basename($notification->type);
            $message = match ($type) {
                'CollaborationInvited' => $this->collaborationInvited($notification),
                default => '',
            };
            $notifications[] = [
                'type' => $type,
                'message' => $message,
                'data' => $notification->data,
            ];
        }

        return view('livewire.layout.notificationMenu', [
            'notifications' => $notifications,
        ]);
    }

    private function collaborationInvited(DatabaseNotification $item): string
    {
        /** @var Vault $vault */
        $vault = Vault::find($item->data['vault_id']);
        /** @var User $user */
        $user = $vault->user;

        return __(sprintf('%s has invited you to join a vault', $user->name));
    }
}
