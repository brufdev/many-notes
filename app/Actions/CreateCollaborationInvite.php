<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\UserNotified;
use App\Models\User;
use App\Models\Vault;
use App\Notifications\CollaborationInvited;

final readonly class CreateCollaborationInvite
{
    public function handle(Vault $vault, User $user): void
    {
        if ($vault->collaborators()->where('user', $user)->count()) {
            return;
        }

        $vault->collaborators()->attach($user, ['accepted' => 0]);
        $user->notify(new CollaborationInvited($vault));
        broadcast(new UserNotified($user));
    }
}
