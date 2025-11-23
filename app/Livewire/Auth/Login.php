<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\GetAvailableOAuthProviders;
use App\Actions\IsLocalAuthEnabled;
use App\Enums\OAuthProvider;
use App\Livewire\Forms\LoginForm;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

final class Login extends Component
{
    public LoginForm $form;

    #[Locked]
    public bool $isRegistrationEnabled;

    /** @var array<int, OAuthProvider> */
    public array $providers;

    public function mount(): void
    {
        $this->isRegistrationEnabled = app(Setting::class)->registration;
        $this->providers = new GetAvailableOAuthProviders()->handle();

        if ($this->providers !== [] && !new IsLocalAuthEnabled()->handle()) {
            try {
                /** @var OAuthProvider $provider */
                $provider = current($this->providers);
                $this->redirect(Socialite::driver($provider->value)->redirect()->getTargetUrl());
            } catch (Throwable) {
                abort(404);
            }
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function send(): void
    {
        $this->form->authenticate();

        Session::regenerate();

        /** @var User $user */
        $user = auth()->user();
        $redirectUrl = mb_strlen((string) $user->last_visited_url) > 0
            ? $user->last_visited_url
            : route('vaults.index', absolute: false);
        $this->redirectIntended($redirectUrl);
    }

    public function render(): Factory|View
    {
        return view('livewire.auth.login');
    }
}
