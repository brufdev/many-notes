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
use Exception;
use Illuminate\Container\Attributes\CurrentUser;
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

    public function update(UpdateVaultRequest $request, Vault $vault, UpdateVault $updateVault): void
    {
        /** @var array{name: string} $data */
        $data = $request->validated();

        $updateVault->handle($vault, $data);
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
