<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultListUpdatedEvent;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Support\Facades\Storage;

final readonly class CreateVault
{
    /** @param array{name: string} $attributes */
    public function handle(User $user, array $attributes, bool $broadcast = true): Vault
    {
        // Generate a new vault name if the current one already exists
        $attributes['name'] = app(GetAvailableVaultName::class)->handle($user, $attributes['name']);

        // Save vault to database
        $vault = $user->vaults()->create($attributes);

        // Save vault to disk
        $vaultPath = app(GetPathFromVault::class)->handle($vault);
        Storage::disk('local')->makeDirectory($vaultPath);

        // Broadcast event
        if ($broadcast) {
            broadcast(new VaultListUpdatedEvent($user))->toOthers();
        }

        return $vault;
    }
}
