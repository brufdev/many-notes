<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

final readonly class GetPathFromUser
{
    public function handle(User $user): string
    {
        return sprintf('private/vaults/%u/', $user->id);
    }
}
