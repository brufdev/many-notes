<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Models\Vault;
use App\Models\VaultNode;
use Livewire\Attributes\On;
use Illuminate\Contracts\View\View;
use App\Livewire\Forms\VaultNodeForm;
use Illuminate\Contracts\View\Factory;

class AddNode extends Modal
{
    public VaultNodeForm $form;

    public bool $show = false;

    public function mount(Vault $vault): void
    {
        $this->authorize('update', $vault);
        $this->form->setVault($vault);
    }

    #[On('open-modal')]
    public function open(VaultNode $parent, bool $isFile = true): void
    {
        if (! is_null($parent->vault)) {
            $this->authorize('update', $parent->vault);
        }

        $this->form->parent_id = $parent->id;
        $this->form->is_file = $isFile;
        $this->form->extension = 'md';
        $this->openModal();
    }

    public function add(): void
    {
        $node = $this->form->create();
        $this->closeModal();
        $this->dispatch('node-updated');
        if ($node->parent_id === $this->form->vault->templates_node_id) {
            $this->dispatch('templates-refresh');
        }
        $message = $this->form->is_file ? __('File created') : __('Folder created');
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function render(): Factory|View
    {
        return view('livewire.modals.addNode');
    }
}
