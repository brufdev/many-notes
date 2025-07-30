<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use App\Actions\GetOAuthPostLogoutRedirectUri;
use App\Actions\IsLocalAuthEnabled;
use App\Livewire\Forms\EditPasswordForm;
use App\Livewire\Forms\EditProfileForm;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class UserMenu extends Component
{
    public EditProfileForm $profileForm;

    public EditPasswordForm $passwordForm;

    #[Locked]
    public bool $localAuthEnabled;

    #[Locked]
    public string $appVersion;

    #[Locked]
    public string $latestVersion;

    #[Locked]
    public string $githubUrl;

    #[Locked]
    public bool $updateAvailable;

    public function mount(): void
    {
        /** @var string $appVersion */
        $appVersion = config('app.version');
        /** @var string $latestVersion */
        $latestVersion = Cache::get('app:latest_version', '0.0.0');

        $this->profileForm->setUser();
        $this->localAuthEnabled = new IsLocalAuthEnabled()->handle();
        $this->githubUrl = 'https://github.com/brufdev/many-notes';
        $this->appVersion = $appVersion;
        $this->latestVersion = $latestVersion;
        $this->updateAvailable = version_compare($this->appVersion, $this->latestVersion, '<');
    }

    public function editProfile(): void
    {
        if (!$this->localAuthEnabled) {
            return;
        }

        $this->profileForm->update();

        $this->dispatch('close-modal');
        $this->dispatch('toast', message: __('Profile updated'), type: 'success');
    }

    public function editPassword(): void
    {
        if (!$this->localAuthEnabled) {
            return;
        }

        $this->passwordForm->update();

        $this->dispatch('close-modal');
        $this->dispatch('toast', message: __('Password updated'), type: 'success');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(): void
    {
        if (!new IsLocalAuthEnabled()->handle()) {
            $postLogoutRedirectUri = new GetOAuthPostLogoutRedirectUri()->handle();
        } else {
            $postLogoutRedirectUri = route('login', absolute: false);
        }

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        $this->redirect($postLogoutRedirectUri);
    }

    public function render(): Factory|View
    {
        return view('livewire.layout.userMenu');
    }
}
