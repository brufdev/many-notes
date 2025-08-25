<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\CreateUser;
use App\Actions\IsLocalAuthEnabled;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Component;

final class OAuthLoginCallback extends Component
{
    public function mount(string $provider): void
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Exception) {
            session()->flash('error', __('An error occurred while authenticating.'));
            $this->redirect(route('login', absolute: false));

            return;
        }

        $userEmail = $socialUser->getEmail() ?? '';
        $userName = $socialUser->getName() ?? $socialUser->getNickname() ?? '';

        if (!filter_var($socialUser->getEmail(), FILTER_VALIDATE_EMAIL)) {
            session()->flash('error', __('No email address found.'));
            $this->redirect(route('login', absolute: false));

            return;
        }

        if ($userName === '') {
            /** @var int<0, max> $atPos */
            $atPos = mb_strrpos($userEmail, '@');
            $userName = mb_substr($userEmail, 0, $atPos);
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
