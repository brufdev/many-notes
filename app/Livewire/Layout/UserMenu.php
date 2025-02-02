<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Forms\EditProfileForm;
use Illuminate\Support\Facades\Session;
use App\Livewire\Forms\EditPasswordForm;

class UserMenu extends Component
{
    public EditProfileForm $profileForm;

    public EditPasswordForm $passwordForm;

    public string $appVersion;

    public string $githubUrl;

    public function mount(): void
    {
        $this->profileForm->setUser();
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

        $this->redirect(route('login', absolute: false), navigate: true);
    }

    public function render()
    {
        return view('livewire.layout.userMenu');
    }
}
