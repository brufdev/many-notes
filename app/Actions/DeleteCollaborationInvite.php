<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Vault;
use App\Notifications\CollaborationInvited;

final readonly class DeleteCollaborationInvite
{
    public function handle(Vault $vault, User $user): void
    {
        $vault->collaborators()->detach($user);
        $notifications = $user->notifications()->where('type', CollaborationInvited::class)->get();
        foreach ($notifications as $notification) {
            if ($notification->data['vault_id'] === $vault->id) {
                $notification->delete();
            }
        }
    }
}
