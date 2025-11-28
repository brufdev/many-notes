<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateVault;
use App\Http\Requests\StoreVaultRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;

final readonly class VaultController
{
    public function store(StoreVaultRequest $request, #[CurrentUser] User $user, CreateVault $createVault): void
    {
        /** @var array{name: string} $data */
        $data = $request->validated();

        $createVault->handle($user, $data);
    }
}
