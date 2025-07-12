<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use App\Livewire\Forms\EditPasswordForm;
use App\Livewire\Forms\EditProfileForm;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

final class UserMenu extends Component
{
    public EditProfileForm $profileForm;

    public EditPasswordForm $passwordForm;

    public string $appVersion;

    public string $latestVersion;

    public string $githubUrl;

    public bool $updateAvailable;

    public function mount(): void
    {
        $this->profileForm->setUser();
        $this->githubUrl = 'https://github.com/brufdev/many-notes';

        /** @var string $appVersion */
        $appVersion = config('app.version');
        /** @var string $latestVersion */
        $latestVersion = Cache::get('app:latest_version', '0.0.0');

        $this->appVersion = $appVersion;
        $this->latestVersion = $latestVersion;
        $this->updateAvailable = version_compare($this->appVersion, $this->latestVersion, '<');
    }

    public function editProfile(): void
    {
        $this->profileForm->update();

        $this->dispatch('close-modal');
        $this->dispatch('toast', message: __('Profile updated'), type: 'success');
    }

    public function editPassword(): void
    {
        $this->passwordForm->update();

        $this->dispatch('close-modal');
        $this->dispatch('toast', message: __('Password updated'), type: 'success');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(): void
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        $this->redirect(route('login', absolute: false));
    }

    public function render(): Factory|View
    {
        return view('livewire.layout.userMenu');
    }
}
