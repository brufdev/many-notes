<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultCollaborationCreatedEvent;
use App\Models\User;
use App\Models\Vault;
use App\Notifications\VaultCollaborationInvitationReceived;

final readonly class CreateVaultCollaboration
{
    public function handle(Vault $vault, User $user): User
    {
        $vault->collaborators()->attach($user, ['accepted' => 0]);

        $collaborator = $vault->collaborators()->wherePivot('user_id', $user->id)->firstOrFail();

        $user->notify(new VaultCollaborationInvitationReceived($vault));

        broadcast(new VaultCollaborationCreatedEvent($vault, $collaborator))->toOthers();

        return $collaborator;
    }
}
