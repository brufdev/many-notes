<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Events\VaultFileSystemUpdatedEvent;
use App\Livewire\Forms\VaultNodeForm;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class AddNode extends Component
{
    use Modal;

    public VaultNodeForm $form;

    public function mount(Vault $vault): void
    {
        $this->authorize('update', $vault);
        $this->form->setVault($vault->id);
    }

    #[On('open-modal')]
    public function open(VaultNode $parent, bool $isFile = true): void
    {
        if ($parent->vault !== null && $parent->vault->id !== $this->form->vaultId) {
            return;
        }

        $this->form->parent_id = $parent->id;
        $this->form->is_file = $isFile;
        $this->form->extension = $isFile ? 'md' : null;
        $this->openModal();
    }

    public function add(): void
    {
        /** @var Vault $vault */
        $vault = Vault::find($this->form->vaultId);
        $node = $this->form->create();
        $this->closeModal();

        if ($node->is_file) {
            $this->dispatch('open-new-file', id: $node->id);
        }

        $message = $node->is_file ? __('File created') : __('Folder created');
        $this->dispatch('toast', message: $message, type: 'success');

        broadcast(new VaultFileSystemUpdatedEvent($vault));
    }

    public function render(): Factory|View
    {
        return view('livewire.modals.addNode');
    }
}
