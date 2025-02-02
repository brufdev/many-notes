<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Models\Vault;
use Livewire\Component;
use App\Models\VaultNode;
use App\Services\VaultFile;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use App\Livewire\Modals\Modal;
use Livewire\Attributes\Validate;
use Illuminate\Contracts\View\View;
use App\Actions\ProcessImportedFile;
use Illuminate\Contracts\View\Factory;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class ImportFile extends Component
{
    use Modal;

    use WithFileUploads;

    public Vault $vault;

    public VaultNode $parent;

    #[Validate]
    public ?TemporaryUploadedFile $file = null;

    public string $fileMimes;

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'mimes:' . implode(',', VaultFile::extensions()),
            ],
        ];
    }

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
        $fileName = $file->getClientOriginalName();
        $filePath = $file->getRealPath();
        new ProcessImportedFile()->handle($this->vault, $this->parent, $fileName, $filePath);
        $this->dispatch('node-updated');
        $this->closeModal();
        $this->dispatch('toast', message: __('File imported'), type: 'success');
    }

    public function render(): Factory|View
    {
        return view('livewire.modals.importFile');
    }
}
