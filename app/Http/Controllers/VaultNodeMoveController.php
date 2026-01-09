<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\MoveVaultNode;
use App\Http\Requests\MoveVaultNodeRequest;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

final readonly class VaultNodeMoveController
{
    public function __invoke(
        MoveVaultNodeRequest $request,
        Vault $vault,
        VaultNode $node,
        #[CurrentUser] User $user,
    ): JsonResponse {
        if ($user->cannot('update', $vault)) {
            abort(403);
        }

        /** @var array{ parent_id: int|null } $data */
        $data = $request->validated();

        if ($node->id === $data['parent_id']) {
            abort(422, 'A node cannot be its own parent');
        }

        if ($data['parent_id']) {
            $parent = $vault->nodes()->where('id', $data['parent_id'])->first();

            if (!$parent) {
                abort(404, 'Parent node not found');
            }

            if ($parent->is_file) {
                abort(422, 'Parent node cannot be a file');
            }
        }

        app(MoveVaultNode::class)->handle($node, $data);

        return response()->json();
    }
}
