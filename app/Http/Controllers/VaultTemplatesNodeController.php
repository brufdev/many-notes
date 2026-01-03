<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateVault;
use App\Http\Requests\UpdateVaultTemplatesNodeRequest;
use App\Models\User;
use App\Models\Vault;
use App\ViewModels\VaultViewModel;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

final readonly class VaultTemplatesNodeController
{
    public function __invoke(
        UpdateVaultTemplatesNodeRequest $request,
        Vault $vault,
        #[CurrentUser] User $user,
        UpdateVault $updateVault,
    ): JsonResponse {
        if ($user->cannot('update', $vault)) {
            abort(403);
        }

        /** @var array{templates_node_id: int} $data */
        $data = $request->validated();

        $node = $vault->nodes()->where('id', $data['templates_node_id'])->first();

        if (!$node || $node->is_file) {
            abort(404);
        }

        if ($vault->templates_node_id === $data['templates_node_id']) {
            $data['templates_node_id'] = null;
        }

        $vault = $updateVault->handle($vault, $data);

        return response()->json([
            'vault' => VaultViewModel::fromModel($vault)->toArray(),
        ]);
    }
}
