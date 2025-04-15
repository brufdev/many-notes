<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\RegisterForm;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class Register extends Component
{
    public RegisterForm $form;

    public function send(): void
    {
        $this->form->register();

        $this->redirect(route('vaults.index', absolute: false));
    }

    public function render(): Factory|View
    {
        return view('livewire.auth.register');
    }
}
