<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Actions\ProcessImportedVault;
use App\Events\VaultListUpdatedEvent;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class ImportVault extends Component
{
    use Modal;
    use WithFileUploads;

    #[Validate('required|file|mimes:zip')]
    public ?TemporaryUploadedFile $file = null;

    #[On('open-modal')]
    public function open(): void
    {
        $this->openModal();
    }

    public function updatedFile(): void
    {
        $this->validate();

        /** @var User $user */
        $user = auth()->user();
        /** @var TemporaryUploadedFile $file */
        $file = $this->file;
        $fileName = $file->getClientOriginalName();
        $filePath = $file->getRealPath();

        new ProcessImportedVault()->handle($user, $fileName, $filePath);
        $this->closeModal();

        $this->dispatch('toast', message: __('Vault imported'), type: 'success');

        broadcast(new VaultListUpdatedEvent($user));
    }

    public function render(): Factory|View
    {
        return view('livewire.modals.importVault');
    }
}
