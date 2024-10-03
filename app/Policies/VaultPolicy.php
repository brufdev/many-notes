<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vault;
use Illuminate\Auth\Access\Response;

class VaultPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vault $vault): bool
    {
        return $user->id === $vault->created_by;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vault $vault): bool
    {
        return $user->id === $vault->created_by;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vault $vault): bool
    {
        return $user->id === $vault->created_by;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Vault $vault): bool
    {
        return $user->id === $vault->created_by;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Vault $vault): bool
    {
        return $user->id === $vault->created_by;
    }
}
