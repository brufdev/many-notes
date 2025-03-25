<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;

final readonly class VaultNodePolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VaultNode $node): bool
    {
        /** @var Vault $vault */
        $vault = $node->vault;

        return $user->id === $vault->created_by ||
            $vault->collaborators()->where('user_id', $user->id)->exists();
    }
}
