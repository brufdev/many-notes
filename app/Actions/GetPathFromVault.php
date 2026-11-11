<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\InvalidVaultPath;
use App\Models\Vault;

final readonly class GetPathFromVault
{
    public function handle(Vault $vault): string
    {
        $vault->loadMissing('user');

        throw_unless(app(IsSafePathSegment::class)->handle($vault->name), InvalidVaultPath::class);

        return sprintf('vaults/%u/%s/', $vault->user->id, $vault->name);
    }
}
