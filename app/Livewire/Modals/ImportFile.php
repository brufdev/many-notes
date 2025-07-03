<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Actions\ProcessImportedFile;
use App\Events\VaultFileSystemUpdatedEvent;
use App\Models\Vault;
use App\Models\VaultNode;
use App\Services\VaultFile;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

final class ImportFile extends Component
{
    use Modal;
    use WithFileUploads;

    public Vault $vault;

    public VaultNode $parent;

    #[Validate('required|file')]
    public ?TemporaryUploadedFile $file = null;

    public string $fileMimes;

    public function mount(Vault $vault): void
    {
        $this->authorize('view', $vault);
        $this->vault = $vault;
        $this->fileMimes = implode(',', VaultFile::extensions(true));
    }

    #[On('open-modal')]
    public function open(VaultNode $parent): void
    {
        $this->parent = $parent;

        if ($this->parent->exists) {
            $this->authorize('update', $this->parent->vault()->first());

            // Make sure submitted parent node is a folder
            if ($this->parent->is_file) {
                abort(400);
            }
        }

        $this->openModal();
    }

    public function updatedFile(): void
    {
        $this->validate();

        /** @var TemporaryUploadedFile $file */
        $file = $this->file;
        $fileExtension = $file->getClientOriginalExtension();
        $fileMimeType = $file->getMimeType();
        $fileName = $file->getClientOriginalName();
        $filePath = $file->getRealPath();

        if (!VaultFile::validate($fileExtension, $fileMimeType)) {
            $this->closeModal();

            $this->dispatch('toast', message: __('File not supported'), type: 'error');

            return;
        }

        new ProcessImportedFile()->handle($this->vault, $this->parent, $fileName, $filePath);
        $this->closeModal();

        $this->dispatch('toast', message: __('File imported'), type: 'success');

        broadcast(new VaultFileSystemUpdatedEvent($this->vault));
    }

    public function render(): Factory|View
    {
        return view('livewire.modals.importFile');
    }
}
