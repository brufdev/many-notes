<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Actions\IsLocalAuthEnabled;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class EditProfileForm extends Form
{
    #[Validate]
    public string $name;

    #[Validate]
    public string $email;

    /**
     * @return array<string, list<string|Unique>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore(auth()->user()),
            ],
        ];
    }

    public function setUser(): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        $this->name = $currentUser->name;
        $this->email = $currentUser->email;
    }

    public function update(): void
    {
        if (!new IsLocalAuthEnabled()->handle()) {
            return;
        }

        /** @var User $currentUser */
        $currentUser = auth()->user();

        $this->name = mb_trim($this->name);
        $this->email = mb_trim($this->email);

        $this->validate();

        $currentUser->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);
    }
}
