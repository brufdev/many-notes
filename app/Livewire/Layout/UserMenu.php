<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use App\Livewire\Forms\EditPasswordForm;
use App\Livewire\Forms\EditProfileForm;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

final class UserMenu extends Component
{
    public EditProfileForm $profileForm;

    public EditPasswordForm $passwordForm;

    public string $appVersion;

    public string $githubUrl;

    public function mount(): void
    {
        $this->profileForm->setUser();
        /** @var array{root: array{pretty_version: string}} $composerInfo */
        $composerInfo = require base_path('vendor/composer/installed.php');
        $this->appVersion = $composerInfo['root']['pretty_version'];
        $this->githubUrl = 'https://github.com/brufdev/many-notes';
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
