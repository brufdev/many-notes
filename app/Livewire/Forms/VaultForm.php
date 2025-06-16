<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Actions\CreateVault;
use App\Actions\UpdateVault;
use App\Events\VaultListUpdatedEvent;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class VaultForm extends Form
{
    public ?int $vaultId = null;

    #[Validate]
    public string $name = '';

    /**
     * @return array<string, list<string|Unique>>
     */
    public function rules(): array
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        return [
            'name' => [
                'required',
                'min:1',
                // One or more allowed characters, not starting with a dot or space
                'regex:/^(?![. ])[\w\s.,;_\-&%#\[\]()=]+$/u',
                Rule::unique(Vault::class)
                    ->where('created_by', $currentUser->id)
                    ->ignore($this->vaultId),
            ],
        ];
    }

    public function setVault(Vault $vault): void
    {
        $this->vaultId = $vault->id;
        $this->name = $vault->name;
    }

    public function create(): void
    {
        $this->validate();

        /** @var User $user */
        $user = auth()->user();

        new CreateVault()->handle($user, [
            'name' => $this->name,
        ]);
        $this->reset(['name']);

        broadcast(new VaultListUpdatedEvent($user));
    }

    public function update(): void
    {
        $vault = Vault::find($this->vaultId);

        if ($vault === null) {
            return;
        }

        $this->validate();

        new UpdateVault()->handle($vault, [
            'name' => $this->name,
        ]);
    }
}
