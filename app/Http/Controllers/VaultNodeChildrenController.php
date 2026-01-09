<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use App\ViewModels\VaultTreeNodeViewModel;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

final readonly class VaultNodeChildrenController
{
    public function __invoke(Vault $vault, VaultNode $node, #[CurrentUser] User $user): JsonResponse
    {
        if ($user->cannot('view', $vault)) {
            abort(403);
        }

        $children = $node->children
            ->map(fn(VaultNode $node): array => VaultTreeNodeViewModel::fromModel($node)->toArray());

        return response()->json([
            'children' => $children,
        ]);
    }
}
