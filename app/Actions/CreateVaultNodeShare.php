<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\VaultNode;
use App\Models\VaultNodeShare;
use Illuminate\Support\Str;

final readonly class CreateVaultNodeShare
{
    public function handle(VaultNode $node): VaultNodeShare
    {
        /** @var VaultNodeShare $share */
        $share = VaultNodeShare::query()->firstOrCreate(
            ['vault_node_id' => $node->id],
            ['token' => Str::random(48)],
        );

        return $share;
    }
}
