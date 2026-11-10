<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Vault;
use Illuminate\Support\Facades\Storage;

final readonly class GetAvailableVaultName
{
    public function handle(User $owner, string $name, ?Vault $ignore = null): string
    {
        $userPath = app(GetPathFromUser::class)->handle($owner);
        $ignoredPath = $ignore instanceof Vault ? mb_strtolower($userPath . $ignore->name) : null;
        $isTaken = fn(string $candidate): bool => $this->isTaken(
            $owner,
            $candidate,
            $ignore,
            $userPath . $candidate,
            $ignoredPath,
        );

        if (!$isTaken($name)) {
            return $name;
        }

        $getSuffixedName = app(GetSuffixedName::class);
        $number = 1;

        while ($isTaken($getSuffixedName->handle($name, $number))) {
            $number++;
        }

        return $getSuffixedName->handle($name, $number);
    }

    private function isTaken(User $owner, string $name, ?Vault $ignore, string $path, ?string $ignoredPath): bool
    {
        $vaults = $owner->vaults()->whereRaw('LOWER(name) = LOWER(?)', [$name]);

        if ($ignore instanceof Vault) {
            $vaults->whereKeyNot($ignore->id);
        }

        if ($vaults->exists()) {
            return true;
        }

        // Check folders without a vault
        return mb_strtolower($path) !== $ignoredPath && Storage::disk('local')->exists($path);
    }
}
