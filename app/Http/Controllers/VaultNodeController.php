<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateVaultNode;
use App\Actions\UpdateVaultNode;
use App\Http\Requests\StoreVaultNodeRequest;
use App\Http\Requests\UpdateVaultNodeRequest;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use App\ViewModels\VaultTreeNodeViewModel;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

final readonly class VaultNodeController
{
    public function store(
        StoreVaultNodeRequest $request,
        Vault $vault,
        #[CurrentUser] User $user,
        CreateVaultNode $createVaultNode,
    ): JsonResponse {
        if ($user->cannot('update', $vault)) {
            abort(403);
        }

        /**
         * @var array{
         *   parent_id?: int|null,
         *   name: string,
         *   is_file: bool,
         * } $data
         */
        $data = $request->validated();
        $data['extension'] = $data['is_file'] ? 'md' : null;

        $node = $createVaultNode->handle($vault, $data);

        return response()->json([
            'node' => VaultTreeNodeViewModel::fromModel($node)->toArray(),
        ]);
    }

    public function update(
        UpdateVaultNodeRequest $request,
        Vault $vault,
        VaultNode $node,
        #[CurrentUser] User $user,
        UpdateVaultNode $updateVaultNode,
    ): JsonResponse {
        if ($user->cannot('update', $vault)) {
            abort(403);
        }

        /** @var array{name: string} $data */
        $data = $request->validated();

        $node = $updateVaultNode->handle($node, $data);

        return response()->json([
            'node' => VaultTreeNodeViewModel::fromModel($node)->toArray(),
        ]);
    }
}
