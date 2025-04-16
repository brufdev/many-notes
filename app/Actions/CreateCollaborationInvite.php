<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Vault;

final readonly class CreateCollaborationInvite
{
    public function handle(Vault $vault, User $user): void
    {
        if ($vault->collaborators()->wherePivot('user_id', $user->id)->count()) {
            return;
        }

        $vault->collaborators()->attach($user, ['accepted' => 0]);
    }
}
