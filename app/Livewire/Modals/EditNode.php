<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Events\VaultFileSystemUpdatedEvent;
use App\Events\VaultNodeUpdatedEvent;
use App\Livewire\Forms\VaultNodeForm;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class EditNode extends Component
{
    use Modal;

    public VaultNodeForm $form;

    public function mount(Vault $vault): void
    {
        $this->authorize('update', $vault);
        $this->form->setVault($vault->id);
    }

    #[On('open-modal')]
    public function open(VaultNode $node): void
    {
        $this->authorize('update', $node->vault);
        $this->form->setNode($node);
        $this->openModal();
    }

    public function edit(): void
    {
        $node = $this->form->update();

        if (!$node instanceof VaultNode) {
            return;
        }

        /** @var Vault $vault */
        $vault = $node->vault;
        $this->closeModal();

        $message = $this->form->is_file ? __('File edited') : __('Folder edited');
        $this->dispatch('toast', message: $message, type: 'success');

        broadcast(new VaultFileSystemUpdatedEvent($vault));
        broadcast(new VaultNodeUpdatedEvent($node));
    }

    public function render(): Factory|View
    {
        return view('livewire.modals.editNode');
    }
}
