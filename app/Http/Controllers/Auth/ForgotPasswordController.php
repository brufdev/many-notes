<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class ForgotPasswordController
{
    public function create(): Response
    {
        return Inertia::render('auth/ForgotPassword');
    }

    /**
     * @throws ValidationException
     */
    public function store(ForgotPasswordRequest $request): void
    {
        /** @var array<string, string> $credentials */
        $credentials = $request->validated();
        $status = Password::sendResetLink($credentials);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        Inertia::flash('status', __($status));
    }
}
