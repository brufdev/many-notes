<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateVaultNodeShare;
use App\Actions\DeleteVaultNodeShare;
use App\Enums\VaultNodeType;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

final readonly class VaultNodeShareController
{
    public function store(
        Vault $vault,
        VaultNode $node,
        #[CurrentUser] User $user,
        CreateVaultNodeShare $createVaultNodeShare,
    ): JsonResponse {
        abort_unless($user->can('share', $node), 403);
        abort_unless($node->is_file && $node->type() === VaultNodeType::NOTE, 422);

        $share = $createVaultNodeShare->handle($node);

        return response()->json([
            'data' => [
                'token' => $share->token,
                'url' => route('share.show', ['share' => $share->token]),
            ],
        ]);
    }

    public function destroy(
        Vault $vault,
        VaultNode $node,
        #[CurrentUser] User $user,
        DeleteVaultNodeShare $deleteVaultNodeShare,
    ): JsonResponse {
        abort_unless($user->can('share', $node), 403);

        $deleteVaultNodeShare->handle($node);

        return response()->json([
            'data' => null,
        ]);
    }
}
