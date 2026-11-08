<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\VaultNode;

final readonly class GetUrlFromVaultNode
{
    public function handle(VaultNode $node): string
    {
        $extension = $node->extension === null ? '' : ".{$node->extension}";

        return '/files/' . $node->vault_id . '?path=' . $node->fullPath() . $extension;
    }
}
