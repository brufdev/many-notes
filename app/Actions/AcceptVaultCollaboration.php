<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\NotificationDeletedEvent;
use App\Events\VaultCollaborationAcceptedEvent;
use App\Models\User;
use App\Models\Vault;
use App\Notifications\VaultCollaborationAccepted;
use App\Notifications\VaultCollaborationInvitationReceived;

final readonly class AcceptVaultCollaboration
{
    public function handle(Vault $vault, User $user): void
    {
        $vault->collaborators()->updateExistingPivot($user->id, ['accepted' => 1]);

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

        $vault->user->notify(new VaultCollaborationAccepted($user));

        $collaborator = $vault->collaborators()->wherePivot('user_id', $user->id)->firstOrFail();

        broadcast(new VaultCollaborationAcceptedEvent($vault, $collaborator));
    }
}
