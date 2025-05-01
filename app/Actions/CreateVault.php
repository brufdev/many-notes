<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Vault;
use Illuminate\Support\Facades\Storage;

final readonly class CreateVault
{
    /**
     * @param array{name: string} $attributes
     */
    public function handle(User $user, array $attributes): Vault
    {
        // Generate a new vault name if the current one already exists
        $vaultExists = $user->vaults()
            ->where('name', 'like', $attributes['name'])
            ->exists();

        if ($vaultExists) {
            /** @var list<string> $vaults */
            $vaults = $user->vaults()
                ->select('name')
                ->where('name', 'like', $attributes['name'] . '-%')
                ->pluck('name')
                ->toArray();
            natcasesort($vaults);
            $attributes['name'] .= count($vaults) && preg_match('/-(\d+)$/', end($vaults), $matches) === 1
                ? '-' . ((int) $matches[1] + 1)
                : '-1';
        }

        // Save vault to database
        $vault = $user->vaults()->create($attributes);

        // Save vault to disk
        $vaultPath = new GetPathFromVault()->handle($vault);
        Storage::disk('local')->makeDirectory($vaultPath);

        return $vault;
    }
}
