<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;

final readonly class ProfileController
{
    public function update(ProfileRequest $request, #[CurrentUser] User $user): void
    {
        $user->update($request->validated());
    }
}
