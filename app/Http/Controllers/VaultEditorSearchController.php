<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SearchVaultFilesForEditor;
use App\Models\User;
use App\Models\Vault;
use App\ViewModels\VaultEditorSearchViewModel;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class VaultEditorSearchController
{
    public function __invoke(
        Request $request,
        Vault $vault,
        #[CurrentUser] User $user,
        SearchVaultFilesForEditor $searchVaultFilesForEditor,
    ): JsonResponse {
        abort_unless($user->can('view', $vault), 403);

        $search = is_string($request->query('search')) ? $request->query('search') : '';
        $searchType = is_string($request->query('searchType')) ? $request->query('searchType') : '';

        if ($searchType !== 'image') {
            $searchType = 'all';
        }

        $results = $searchVaultFilesForEditor->handle($vault, $search, $searchType);

        $files = $results->map(VaultEditorSearchViewModel::fromModel(...));

        return response()->json([
            'data' => [
                'files' => $files,
            ],
        ]);
    }
}
