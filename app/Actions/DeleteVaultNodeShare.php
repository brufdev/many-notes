<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\VaultNode;

final readonly class DeleteVaultNodeShare
{
    public function handle(VaultNode $node): void
    {
        $node->share()->delete();
    }
}
