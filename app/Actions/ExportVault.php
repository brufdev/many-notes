<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;

final readonly class ExportVault
{
    public function handle(Vault $vault): ?string
    {
        $vaultPath = app(GetPathFromVault::class)->handle($vault);

        return app(CreateZipFromDirectory::class)->handle($vaultPath);
    }
}
