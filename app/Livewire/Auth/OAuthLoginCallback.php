<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\CreateUser;
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
            $providerUser = Socialite::driver($provider)->user();
        } catch (Exception) {
            session()->flash('error', __('An error occurred while authenticating.'));
            $this->redirect('/login', true);

            return;
        }

        if (!filter_var($providerUser->getEmail(), FILTER_VALIDATE_EMAIL)) {
            session()->flash('error', __('No email address found.'));
            $this->redirect('/login', true);

            return;
        }

        $user = User::query()->where('email', $providerUser->getEmail())->first();
        if (!$user) {
            $user = new CreateUser()->handle([
                'name' => $providerUser->getName() ?? '',
                'email' => $providerUser->getEmail() ?? '',
                'password' => Hash::make(Str::random(32)),
            ]);
        }
        Auth::login($user);
        $this->redirectIntended(route('vaults.last', absolute: false), true);
    }
}
