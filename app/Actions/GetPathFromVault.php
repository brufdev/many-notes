<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;

final readonly class GetPathFromVault
{
    public function handle(Vault $vault): string
    {
        $vault->loadMissing('user');

        return sprintf('private/vaults/%u/%s/', $vault->user->id, $vault->name);
    }
}
