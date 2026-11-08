<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Scopes\SupportedVaultNodeScope;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use App\ViewModels\VaultNodeViewModel;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

final readonly class VaultNodeChildrenController
{
    public function __invoke(Vault $vault, VaultNode $node, #[CurrentUser] User $user): JsonResponse
    {
        if ($user->cannot('view', $vault)) {
            abort(403);
        }

        $children = $node->children()
            ->withoutGlobalScope(SupportedVaultNodeScope::class)
            ->get()
            ->map(fn(VaultNode $node): VaultNodeViewModel => VaultNodeViewModel::fromModel($node));

        return response()->json([
            'children' => $children,
        ]);
    }
}
