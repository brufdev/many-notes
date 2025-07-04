<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vault;

final readonly class VaultPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vault $vault): bool
    {
        return $user->id === $vault->created_by ||
            $vault->collaborators()
                ->wherePivot('user_id', $user->id)
                ->wherePivot('accepted', true)
                ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vault $vault): bool
    {
        return $user->id === $vault->created_by ||
            $vault->collaborators()
                ->wherePivot('user_id', $user->id)
                ->wherePivot('accepted', true)
                ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vault $vault): bool
    {
        return $user->id === $vault->created_by;
    }
}
