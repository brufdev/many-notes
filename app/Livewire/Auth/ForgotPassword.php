<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\ForgotPasswordForm;
use Livewire\Component;

final class ForgotPassword extends Component
{
    public ForgotPasswordForm $form;

    public function send(): void
    {
        $this->form->sendPasswordResetLink();
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
