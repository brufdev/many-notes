<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

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
        $this->form->setVault($vault);
    }

    #[On('open-modal')]
    public function open(VaultNode $parent, bool $isFile = true): void
    {
        if (!is_null($parent->vault)) {
            $this->authorize('update', $parent->vault);
        }

        $this->form->parent_id = $parent->id;
        $this->form->is_file = $isFile;
        $this->form->extension = $isFile ? 'md' : null;
        $this->openModal();
    }

    public function add(): void
    {
        $this->form->create();
        $this->closeModal();
        $this->dispatch('node-updated');
        $message = $this->form->is_file ? __('File created') : __('Folder created');
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function render(): Factory|View
    {
        return view('livewire.modals.addNode');
    }
}
