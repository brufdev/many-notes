<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PasswordRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Hash;

final readonly class PasswordController
{
    public function update(PasswordRequest $request, #[CurrentUser] User $user): void
    {
        $user->update([
            'password' => Hash::make($request->safe()->string('password')->toString()),
        ]);
    }
}
