<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\CreateUser;
use App\Actions\IsLocalAuthEnabled;
use App\Enums\OAuthProvider;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Component;
use SocialiteProviders\Azure\User as AzureUser;

final class OAuthLoginCallback extends Component
{
    public function mount(string $provider): void
    {
        try {
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
                ?? explode('@', $userEmail)[0];
        } catch (Exception) {
            session()->flash('error', __('An error occurred while authenticating.'));
            $this->redirect(route('login', absolute: false));

            return;
        }

        if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            session()->flash('error', __('No email address found.'));
            $this->redirect(route('login', absolute: false));

            return;
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
                    $this->redirect(route('login', absolute: false));

                    return;
                }

                $user = new CreateUser()->handle([
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

        if (!new IsLocalAuthEnabled()->handle()) {
            $user->name = $userName;
            $user->email = $userEmail;
            $user->save();
        }

        Auth::login($user, true);

        $redirectUrl = mb_strlen((string) $user->last_visited_url) > 0
            ? $user->last_visited_url
            : route('vaults.index', absolute: false);
        $this->redirectIntended($redirectUrl);
    }
}
