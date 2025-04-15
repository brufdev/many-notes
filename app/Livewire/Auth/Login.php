<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\GetAvailableOAuthProviders;
use App\Enums\OAuthProviders;
use App\Livewire\Forms\LoginForm;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

final class Login extends Component
{
    public LoginForm $form;

    /** @var array<int, OAuthProviders> */
    public array $providers;

    public function mount(): void
    {
        $this->providers = new GetAvailableOAuthProviders()->handle();
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
