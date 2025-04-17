<?php

declare(strict_types=1);

namespace App\Livewire\Vault;

use App\Events\VaultFileSystemUpdatedEvent;
use App\Livewire\Forms\VaultForm;
use App\Models\Vault;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class Row extends Component
{
    public VaultForm $form;

    #[Locked]
    public int $vaultId;

    public function mount(): void
    {
        $this->form->setVault($this->vault);
    }

    #[Computed]
    public function vault(): Vault
    {
        return Vault::findOrFail($this->vaultId);
    }

    public function update(): void
    {
        $this->authorize('update', $this->vault);

        $this->validate();

        $this->form->update();
        unset($this->vault);

        $this->dispatch('close-modal');
        $this->dispatch('toast', message: __('Vault edited'), type: 'success');

        broadcast(new VaultFileSystemUpdatedEvent($this->vault));
    }

    public function render(): Factory|View
    {
        return view('livewire.vault.row');
    }
}
