<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\ResetPasswordForm;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ResetPassword extends Component
{
    public ResetPasswordForm $form;

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->form->setToken($token);
        $this->form->setEmail(request()->string('email')->toString());
    }

    public function send(): void
    {
        if (!$this->form->resetPassword()) {
            return;
        }

        $this->redirect(route('login', absolute: false));
    }

    public function render(): Factory|View
    {
        return view('livewire.auth.reset-password');
    }
}
