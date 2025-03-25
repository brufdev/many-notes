<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Actions\CreateCollaborationInvite;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class CollaborationInviteForm extends Form
{
    public Vault $vault;

    #[Validate]
    public string $email = '';

    /**
     * @return array<string, list<string|Exists>>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::exists('users', 'email'),
            ],
        ];
    }

    public function setVault(Vault $vault): void
    {
        $this->vault = $vault;
    }

    public function create(): void
    {
        $this->validate();
        /** @var User $user */
        $user = User::where('email', $this->email)->first();
        new CreateCollaborationInvite()->handle($this->vault, $user);
        $this->reset(['email']);
    }
}
