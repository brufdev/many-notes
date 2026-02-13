<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DeclineVaultCollaboration;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Container\Attributes\CurrentUser;

final readonly class VaultCollaborationDeclineController
{
    public function __invoke(
        Vault $vault,
        #[CurrentUser] User $currentUser,
        DeclineVaultCollaboration $declineVaultCollaboration,
    ): void {
        $invitationExists = $vault->collaborators()
            ->wherePivot('user_id', $currentUser->id)
            ->wherePivot('accepted', false)
            ->exists();

        abort_unless($invitationExists, 404);

        $declineVaultCollaboration->handle($vault, $currentUser);
    }
}
