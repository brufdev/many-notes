<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\NotificationDeletedEvent;
use App\Events\VaultCollaborationDeletedEvent;
use App\Models\User;
use App\Models\Vault;
use App\Notifications\VaultCollaborationDeclined;
use App\Notifications\VaultCollaborationInvitationReceived;

final readonly class DeclineVaultCollaboration
{
    public function handle(Vault $vault, User $user): void
    {
        $vault->collaborators()->detach($user->id);

        // Notifications and events
        $notifications = $user->notifications()
            ->where('type', VaultCollaborationInvitationReceived::class)
            ->get();

        foreach ($notifications as $notification) {
            if ($notification->data['vault_id'] === $vault->id) {
                $notification->delete();

                broadcast(new NotificationDeletedEvent($user, $notification))->toOthers();
            }
        }

        $vault->user->notify(new VaultCollaborationDeclined($user));

        broadcast(new VaultCollaborationDeletedEvent($vault, $user));
    }
}
