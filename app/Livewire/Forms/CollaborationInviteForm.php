<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Actions\CreateCollaborationInvite;
use App\Events\UserNotifiedEvent;
use App\Models\User;
use App\Models\Vault;
use App\Notifications\CollaborationInvited;
use Exception;
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

        /** @var User $currentUser */
        $currentUser = auth()->user();
        /** @var User $user */
        $user = User::where('email', $this->email)->first();

        if ($currentUser->id === $user->id) {
            throw new Exception(__('You are the owner of this vault.'));
        }

        if ($this->vault->collaborators()->wherePivot('user_id', $user->id)->count()) {
            throw new Exception(__('This user is already invited.'));
        }

        new CreateCollaborationInvite()->handle($this->vault, $user);
        $this->reset(['email']);

        $user->notify(new CollaborationInvited($this->vault));

        broadcast(new UserNotifiedEvent($user));
    }
}
