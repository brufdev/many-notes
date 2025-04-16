<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use App\Models\User;
use App\Models\Vault;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;

final class NotificationMenu extends Component
{
    public function delete(DatabaseNotification $notification): void
    {
        /** @var User $user */
        $user = auth()->user();
        /** @var User $notifiedUser */
        $notifiedUser = $notification->notifiable;

        if (!$user->is($notifiedUser)) {
            return;
        }

        $notification->delete();
    }

    public function render(): Factory|View
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $notifications = [];

        foreach ($currentUser->notifications as $notification) {
            $type = class_basename($notification->type);
            $message = match ($type) {
                'CollaborationInvited' => $this->collaborationInvited($notification),
                'CollaborationAccepted' => $this->collaborationAccepted($notification),
                'CollaborationDeclined' => $this->collaborationDeclined($notification),
                default => '',
            };
            $notifications[] = [
                'id' => $notification->id,
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

        return __(sprintf('%s invited you to collaborate', $user->name));
    }

    private function collaborationAccepted(DatabaseNotification $item): string
    {
        /** @var User $user */
        $user = User::find($item->data['user_id']);

        return __(sprintf('%s accepted collaborating', $user->name));
    }

    private function collaborationDeclined(DatabaseNotification $item): string
    {
        /** @var User $user */
        $user = User::find($item->data['user_id']);

        return __(sprintf('%s declined collaborating', $user->name));
    }
}
