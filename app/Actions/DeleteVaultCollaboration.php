<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\NotificationDeletedEvent;
use App\Events\VaultCollaborationAccessRevokedEvent;
use App\Events\VaultCollaborationDeletedEvent;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultCollaborator;
use App\Notifications\VaultCollaborationInvitationReceived;

final readonly class DeleteVaultCollaboration
{
    /** @param User&object{pivot: VaultCollaborator} $user */
    public function handle(Vault $vault, User $user): void
    {
        $vault->collaborators()->detach($user);

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

        broadcast(new VaultCollaborationDeletedEvent($vault, $user))->toOthers();

        if ($user->pivot->accepted) {
            broadcast(new VaultCollaborationAccessRevokedEvent($vault, $user));
        }
    }
}
