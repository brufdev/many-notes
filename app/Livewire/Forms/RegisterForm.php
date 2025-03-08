<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Actions\CreateUser;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Form;

final class RegisterForm extends Form
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        /** @var array<string, string> $validated */
        $validated = $this->validate();
        $validated['password'] = Hash::make($validated['password']);
        event(new Registered($user = new CreateUser()->handle($validated)));
        Auth::login($user);
    }
}
