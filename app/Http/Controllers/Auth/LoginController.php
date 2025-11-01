<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\GetAvailableOAuthProviders;
use App\Actions\GetOAuthPostLogoutRedirectUri;
use App\Actions\IsLocalAuthEnabled;
use App\Enums\OAuthProvider;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

final readonly class LoginController
{
    public function create(
        Request $request,
        GetAvailableOAuthProviders $getAvailableOAuthProviders,
        IsLocalAuthEnabled $isLocalAuthEnabled
    ): Response|SymfonyResponse {
        $providers = array_values(array_map(
            fn(OAuthProvider $provider): array => $provider->toArray(),
            $getAvailableOAuthProviders->handle(),
        ));

        if ($providers !== [] && !$isLocalAuthEnabled->handle()) {
            try {
                $url = Socialite::driver($providers[0]['value'])->redirect()->getTargetUrl();

                return redirect()->intended($url);
            } catch (Throwable) {
                abort(404);
            }
        }

        return Inertia::render('auth/Login', [
            'status' => $request->session()->get('status'),
            'error' => $request->session()->get('error'),
            'providers' => $providers,
            'canResetPassword' => Route::has('forgot.password'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $request->validateCredentials();

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        $redirectUrl = mb_strlen((string) $user->last_visited_url) > 0
            ? $user->last_visited_url
            : route('vaults.index', absolute: false);

        return redirect()->intended($redirectUrl);
    }

    public function destroy(
        Request $request,
        IsLocalAuthEnabled $isLocalAuthEnabled,
        GetOAuthPostLogoutRedirectUri $getOAuthPostLogoutRedirectUri,
    ): RedirectResponse {
        if (!$isLocalAuthEnabled->handle()) {
            $postLogoutRedirectUri = $getOAuthPostLogoutRedirectUri->handle();
        } else {
            $postLogoutRedirectUri = route('login', absolute: false);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($postLogoutRedirectUri);
    }
}
