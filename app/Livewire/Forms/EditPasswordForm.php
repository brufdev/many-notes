<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Actions\IsLocalAuthEnabled;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class EditPasswordForm extends Form
{
    #[Validate]
    public string $current_password = '';

    #[Validate]
    public string $password = '';

    #[Validate]
    public string $password_confirmation = '';

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ];
    }

    public function update(): void
    {
        if (!new IsLocalAuthEnabled()->handle()) {
            return;
        }

        try {
            /** @var array<string, string> $validated */
            $validated = $this->validate();
        } catch (ValidationException $e) {
            if (Arr::exists($e->errors(), 'passwordForm.current_password')) {
                $this->reset('current_password');
            }
            $this->reset('password', 'password_confirmation');

            throw $e;
        }

        $this->reset('current_password', 'password', 'password_confirmation');

        /** @var User $currentUser */
        $currentUser = auth()->user();
        $currentUser->update([
            'password' => Hash::make($validated['password']),
        ]);
    }
}
