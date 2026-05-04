<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateVaultNode;
use App\Http\Requests\MoveVaultNodeRequest;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use App\ViewModels\VaultNodeViewModel;
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
        abort_unless($user->can('update', $vault), 403);

        /** @var array{ parent_id: int|null } $data */
        $data = $request->validated();

        abort_if($node->id === $data['parent_id'], 422, 'A node cannot be its own parent');

        if ($data['parent_id']) {
            $parent = $vault->nodes()->where('id', $data['parent_id'])->first();

            abort_if(!$parent, 404, 'Parent node not found');

            abort_if($parent->is_file, 422, 'Parent node cannot be a file');
        }

        $updatedNode = app(UpdateVaultNode::class)->handle($node, $data);

        return response()->json([
            'data' => VaultNodeViewModel::fromModel($updatedNode),
        ]);
    }
}
