<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateVault;
use App\Actions\DeleteVault;
use App\Actions\GetVaultNodeFromPath;
use App\Actions\ResolveTwoPaths;
use App\Actions\UpdateVault;
use App\Http\Requests\StoreVaultRequest;
use App\Http\Requests\UpdateVaultRequest;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use App\Queries\Vaults\VisibleVaultsQuery;
use App\ViewModels\VaultDataViewModel;
use App\ViewModels\VaultNodeViewModel;
use App\ViewModels\VaultOpenedFileDataViewModel;
use App\ViewModels\VaultOpenedFileTreeDataViewModel;
use App\ViewModels\VaultUpdateViewModel;
use App\ViewModels\VaultViewModel;
use Exception;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class VaultController
{
    public function index(#[CurrentUser] User $user, VisibleVaultsQuery $visibleVaultsQuery): Response
    {
        $user->update([
            'last_visited_url' => route('vaults.index', absolute: false),
        ]);

        return Inertia::render('vault/Index', [
            'visibleVaults' => $visibleVaultsQuery($user)->get(),
        ]);
    }

    public function store(StoreVaultRequest $request, #[CurrentUser] User $user, CreateVault $createVault): void
    {
        /** @var array{name: string} $data */
        $data = $request->validated();

        $createVault->handle($user, $data);
    }

    public function show(
        Request $request,
        Vault $vault,
        #[CurrentUser] User $user,
    ): Response {
        abort_unless($user->can('view', $vault), 403);

        $data = [
            'vault' => VaultViewModel::fromModel($vault),
            ...(array) VaultDataViewModel::fromModel($vault),
        ];

        $file = $request->query('file');
        $path = $request->query('path');

        if (is_string($file)) {
            $file = $vault
                ->nodes()
                ->where('id', $file)
                ->where('is_file', true)
                ->firstOrFail();

            if (is_string($path)) {
                $resolvedPath = app(ResolveTwoPaths::class)->handle($file->fullPath(), $path);
                $file = app(GetVaultNodeFromPath::class)->handle($vault->id, $resolvedPath);

                abort_unless($file !== null, 404);
            }

            $data = [
                ...$data,
                'openedFile' => [
                    'file' => VaultNodeViewModel::fromModel($file),
                    ...(array) VaultOpenedFileDataViewModel::fromModel($file),
                    ...(array) VaultOpenedFileTreeDataViewModel::fromModel($vault, $file),
                ],
            ];
        }

        $currentUrl = route('vaults.show', ['vault' => $vault->id], false)
            . ($file instanceof VaultNode ? '?file=' . $file->id : '');

        $user->update([
            'last_visited_url' => $currentUrl,
        ]);

        return Inertia::render('vault/Show', $data);
    }

    public function update(
        UpdateVaultRequest $request,
        Vault $vault,
        #[CurrentUser] User $currentUser,
        UpdateVault $updateVault,
    ): JsonResponse {
        abort_unless($currentUser->can('update', $vault), 403);

        /** @var array{name?: string, templates_node_id?: int} $data */
        $data = $request->validated();

        $vault = $updateVault->handle($vault, $data);

        return response()->json([
            'data' => VaultUpdateViewModel::fromModel($vault)->toArray(),
        ]);
    }

    public function destroy(Vault $vault, #[CurrentUser] User $user, DeleteVault $deleteVault): void
    {
        if ($user->cannot('delete', $vault)) {
            throw ValidationException::withMessages([
                'delete' => __('Not allowed'),
            ]);
        }

        try {
            $deleteVault->handle($vault);
        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'delete' => $e->getMessage(),
            ]);
        }
    }
}
