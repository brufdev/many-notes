<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Vault;
use App\Notifications\CollaborationAccepted;
use App\Notifications\CollaborationInvited;

final readonly class AcceptCollaborationInvite
{
    public function handle(Vault $vault, User $user): bool
    {
        $inviteExists = $vault->collaborators()
            ->where('user_id', $user->id)
            ->wherePivot('accepted', false)
            ->exists();

        if (!$inviteExists) {
            return false;
        }

        $vault->collaborators()->updateExistingPivot($user->id, ['accepted' => 1]);
        $notifications = $user->notifications()->where('type', CollaborationInvited::class)->get();

        foreach ($notifications as $notification) {
            if ($notification->data['vault_id'] === $vault->id) {
                $notification->delete();
            }
        }

        /** @var User $vaultOwner */
        $vaultOwner = $vault->user;
        $vaultOwner->notify(new CollaborationAccepted($vault, $user));

        return true;
    }
}
