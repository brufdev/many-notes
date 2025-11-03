<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\CreateUser;
use App\Actions\GetAvailableOAuthProviders;
use App\Actions\IsLocalAuthEnabled;
use App\Enums\OAuthProvider;
use App\Http\Requests\Auth\OAuthRequest;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Azure\User as AzureUser;
use Symfony\Component\HttpFoundation\Response;

final readonly class OAuthController
{
    public function create(
        OAuthRequest $request,
        GetAvailableOAuthProviders $getAvailableOAuthProviders,
    ): Response {
        try {
            $provider = $request->safe()->string('provider')->value();
            $isValidProvider = in_array(
                $provider,
                array_map(
                    fn(OAuthProvider $provider): string => $provider->value,
                    $getAvailableOAuthProviders->handle(),
                ),
            );

            if (!$isValidProvider) {
                throw new Exception();
            }

            $url = Socialite::driver($provider)->redirect()->getTargetUrl();

            return Inertia::location($url);
        } catch (Exception) {
            abort(404);
        }
    }

    public function store(
        OAuthRequest $request,
        CreateUser $createUser,
        IsLocalAuthEnabled $isLocalAuthEnabled,
    ): RedirectResponse {
        try {
            $provider = $request->safe()->string('provider')->value();
            $socialProvider = Socialite::driver($provider);

            if ($provider === OAuthProvider::Azure->value) {
                /** @var AzureUser $socialUser */
                $socialUser = $socialProvider->user();
                $userEmail = $socialUser->getMail();
            } else {
                $socialUser = $socialProvider->user();
                $userEmail = $socialUser->getEmail() ?? '';
            }

            $userName = $socialUser->getName()
                ?? $socialUser->getNickname()
                ?? explode('@', (string) $userEmail)[0];
        } catch (Exception) {
            session()->flash('error', __('An error occurred while authenticating.'));

            return redirect(route('login', absolute: false));
        }

        if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            session()->flash('error', __('No email address found.'));

            return redirect(route('login', absolute: false));
        }

        $socialAccount = SocialAccount::firstOrNew([
            'provider_name' => $provider,
            'provider_user_id' => $socialUser->getId(),
        ]);

        if (!$socialAccount->exists) {
            $user = User::query()->where('email', $userEmail)->first();

            if (!$user) {
                if (!app(Setting::class)->registration) {
                    session()->flash('error', __('Registration is currently disabled.'));

                    return redirect(route('login', absolute: false));
                }

                $user = $createUser->handle([
                    'name' => $userName,
                    'email' => $userEmail,
                    'password' => Hash::make(Str::random(32)),
                ]);
            }

            $socialAccount->user()->associate($user);
        }

        /** @phpstan-ignore-next-line */
        $socialAccount->refresh_token = $socialUser->refreshToken ?? null;
        /** @phpstan-ignore-next-line */
        $socialAccount->token_expires_at = now()->addSeconds($socialUser->expiresIn ?? 0);
        $socialAccount->save();

        /** @var User $user */
        $user = $socialAccount->user;

        if (!$isLocalAuthEnabled->handle()) {
            $user->name = $userName;
            $user->email = $userEmail;
            $user->save();
        }

        Auth::login($user, true);

        $redirectUrl = mb_strlen((string) $user->last_visited_url) > 0
            ? $user->last_visited_url
            : route('vaults.index', absolute: false);

        return redirect()->intended($redirectUrl);
    }
}
