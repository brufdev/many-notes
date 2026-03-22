<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SearchVaultFilesByTag;
use App\Actions\SearchVaultFilesByText;
use App\Models\User;
use App\Models\Vault;
use App\ViewModels\VaultSearchViewModel;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class VaultSearchController
{
    public function __invoke(
        Request $request,
        Vault $vault,
        #[CurrentUser] User $user,
        SearchVaultFilesByText $searchVaultFilesByText,
        SearchVaultFilesByTag $searchVaultFilesByTag,
    ): JsonResponse {
        abort_unless($user->can('view', $vault), 403);

        $search = is_string($request->query('search')) ? $request->query('search') : '';

        preg_match('/tag:([\p{L}0-9_-]+)/u', $search, $matches);

        if ($matches === []) {
            $results = $searchVaultFilesByText->handle($vault, $search);

            $files = array_map(VaultSearchViewModel::fromTextSearch(...), $results);
        } else {
            $results = $searchVaultFilesByTag->handle($vault, $matches[1]);

            $files = $results->map(VaultSearchViewModel::fromTagSearch(...));
        }

        return response()->json([
            'data' => [
                'files' => $files,
            ],
        ]);
    }
}
