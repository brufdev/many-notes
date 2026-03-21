<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SearchVaultFiles;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class VaultSearchController
{
    public function __invoke(
        Request $request,
        Vault $vault,
        #[CurrentUser] User $user,
        SearchVaultFiles $searchVaultFiles,
    ): JsonResponse {
        abort_unless($user->can('view', $vault), 403);

        $search = is_string($request->query('search')) ? $request->query('search') : '';

        $files = $searchVaultFiles->handle($vault, $search);

        return response()->json([
            'data' => [
                'files' => $files,
            ],
        ]);
    }
}
