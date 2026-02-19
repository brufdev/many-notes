<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateVault;
use App\Actions\DeleteVault;
use App\Actions\UpdateVault;
use App\Http\Requests\StoreVaultRequest;
use App\Http\Requests\UpdateVaultRequest;
use App\Models\User;
use App\Models\Vault;
use App\Queries\Vaults\VisibleVaultsQuery;
use App\ViewModels\VaultUpdateViewModel;
use Exception;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
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

    public function show(): void
    {
        //
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

    public function destroy(#[CurrentUser] User $user, Vault $vault, DeleteVault $deleteVault): void
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
