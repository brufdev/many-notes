<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\CreateUser;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class RegisterController
{
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * @throws ValidationException
     */
    public function store(RegisterRequest $request, CreateUser $action): RedirectResponse
    {
        /** @var array<string, string> $validated */
        $validated = $request->safe()->all();
        $validated['password'] = Hash::make($validated['password']);

        $user = $action->handle($validated);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('vaults.index', absolute: false));
    }
}
