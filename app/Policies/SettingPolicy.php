<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final readonly class SettingPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->isAdmin();
    }
}
