<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\User;
use App\ViewModels\UserViewModel;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

final readonly class ProfileController
{
    public function update(ProfileRequest $request, #[CurrentUser] User $user): JsonResponse
    {
        $user->update($request->validated());

        return response()->json([
            'user' => UserViewModel::fromModel($user)->toArray(),
        ]);
    }
}
