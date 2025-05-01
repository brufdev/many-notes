<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Vault;

final readonly class GetPathFromVault
{
    public function handle(Vault $vault): string
    {
        /** @var User $user */
        $user = $vault->load('user')->user;

        return sprintf('private/vaults/%u/%s/', $user->id, $vault->name);
    }
}
