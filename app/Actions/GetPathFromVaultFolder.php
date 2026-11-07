<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;
use App\Models\VaultNode;

final readonly class GetPathFromVaultFolder
{
    public function handle(Vault $vault, ?VaultNode $folder): string
    {
        return $folder instanceof VaultNode
            ? app(GetPathFromVaultNode::class)->handle($folder) . '/'
            : app(GetPathFromVault::class)->handle($vault);
    }
}
