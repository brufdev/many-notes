<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('User.{userId}', fn(User $user, int $userId): bool => (int) $user->id === $userId);

Broadcast::channel('Vault.{vaultId}', function (User $user, int $vaultId): bool {
    $vault = Vault::find($vaultId);

    if ($vault === null) {
        return false;
    }

    return $user->can('update', $vault);
});

Broadcast::channel('VaultNode.{nodeId}', function (User $user, int $nodeId): ?array {
    $node = VaultNode::find($nodeId);

    if ($node instanceof VaultNode && $user->can('update', $node->vault)) {
        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }

    return null;
});
