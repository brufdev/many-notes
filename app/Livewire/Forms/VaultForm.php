<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Actions\CreateVault;
use App\Actions\UpdateVault;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class VaultForm extends Form
{
    public ?Vault $vault = null;

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
                'min:3',
                'regex:/^[\w]+[\s\w._\-\&\%\#\[\]\(\)]+$/u',
                Rule::unique(Vault::class)
                    ->where('created_by', $currentUser->id)
                    ->ignore($this->vault),
            ],
        ];
    }

    public function setVault(Vault $vault): void
    {
        $this->vault = $vault;
        $this->name = $vault->name;
    }

    public function create(): void
    {
        $this->name = mb_trim($this->name);
        $this->validate();
        /** @var User $currentUser */
        $currentUser = auth()->user();
        new CreateVault()->handle($currentUser, [
            'name' => $this->name,
        ]);
        $this->reset(['name']);
    }

    public function update(): void
    {
        if (is_null($this->vault)) {
            return;
        }

        $this->name = mb_trim($this->name);
        $this->validate();

        new UpdateVault()->handle($this->vault, [
            'name' => $this->name,
        ]);
    }
}
