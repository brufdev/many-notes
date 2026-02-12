<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AcceptVaultCollaboration;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Container\Attributes\CurrentUser;

final readonly class VaultCollaborationAcceptController
{
    public function __invoke(
        Vault $vault,
        #[CurrentUser] User $currentUser,
        AcceptVaultCollaboration $acceptVaultCollaboration,
    ): void {
        $invitationExists = $vault->collaborators()
            ->wherePivot('user_id', $currentUser->id)
            ->wherePivot('accepted', false)
            ->exists();

        abort_unless($invitationExists, 404);

        $acceptVaultCollaboration->handle($vault, $currentUser);
    }
}
