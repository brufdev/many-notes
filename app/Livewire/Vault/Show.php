<?php

declare(strict_types=1);

namespace App\Livewire\Vault;

use App\Actions\DeleteVaultNode;
use App\Actions\GetUrlFromVaultNode;
use App\Actions\GetVaultNodeFromPath;
use App\Actions\ResolveTwoPaths;
use App\Actions\UpdateVault;
use App\Livewire\Forms\VaultNodeForm;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Throwable;

final class Show extends Component
{
    public Vault $vault;

    public VaultNodeForm $nodeForm;

    #[Url(as: 'file', history: true)]
    public ?int $selectedFile = null;

    #[Locked]
    public ?string $selectedFileExtension = null;

    #[Locked]
    public ?string $selectedFileUrl = null;

    public function mount(Vault $vault): void
    {
        $this->authorize('view', $vault);
        $this->nodeForm->setVault($this->vault);
        $this->openFileId($this->selectedFile);
    }

    public function updatedSelectedFile(): void
    {
        $this->openFileId($this->selectedFile);
    }

    public function openFileId(?int $fileId = null): void
    {
        if ($fileId === null) {
            $this->selectedFile = null;
            $this->setLastVisitedUrl();

            return;
        }

        $node = $this->vault->nodes()
            ->where('id', $fileId)
            ->where('is_file', true)
            ->first();

        if ($node === null) {
            abort(404);
        }

        $this->openFile($node);
    }

    public function openFilePath(string $path): void
    {
        /** @var string $currentPath */
        $currentPath = is_null($this->nodeForm->node)
            ? ''
            /** @phpstan-ignore-next-line larastan.noUnnecessaryCollectionCall */
            : $this->nodeForm->node->ancestorsAndSelf()->get()->last()->full_path;
        $resolvedPath = new ResolveTwoPaths()->handle($currentPath, $path);
        $node = new GetVaultNodeFromPath()->handle($this->vault->id, $resolvedPath);

        if (is_null($node)) {
            abort(404);
        }

        $this->openFile($node);
    }

    #[On('file-refresh')]
    public function refreshFile(VaultNode $node): void
    {
        if ($node->id !== $this->selectedFile) {
            return;
        }

        $this->setNode($node);
    }

    public function closeFile(): void
    {
        $this->reset(['selectedFile', 'selectedFileExtension', 'selectedFileUrl']);
        $this->nodeForm->reset('node');
    }

    public function updated(string $name): void
    {
        $node = $this->nodeForm->node;

        if (!str_starts_with($name, 'nodeForm') || is_null($node)) {
            return;
        }

        $this->nodeForm->update();
        $this->setNode($node);

        if ($node->wasChanged(['parent_id', 'name'])) {
            $this->dispatch('node-updated');
        }
    }

    public function setTemplateFolder(VaultNode $node): void
    {
        $this->authorize('update', $node->vault);

        if (!$node->vault || $this->vault->id !== $node->vault->id || $node->is_file) {
            $this->dispatch('toast', message: __('Something went wrong'), type: 'error');

            return;
        }

        new UpdateVault()->handle($this->vault, [
            'templates_node_id' => $node->id,
        ]);
        $this->dispatch('toast', message: __('Template folder updated'), type: 'success');
    }

    public function deleteNode(VaultNode $node): void
    {
        $this->authorize('delete', $node);

        try {
            $deletedNodes = new DeleteVaultNode()->handle($node);
            $this->dispatch('node-updated');

            $openFileDeleted = !is_null(
                array_find(
                    $deletedNodes,
                    fn (VaultNode $node): bool => $node->id === $this->selectedFile,
                )
            );

            if ($openFileDeleted) {
                $this->closeFile();
            }

            $message = $node->is_file ? __('File deleted') : __('Folder deleted');
            $this->dispatch('toast', message: $message, type: 'success');
        } catch (Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(): Factory|View
    {
        return view('livewire.vault.show');
    }

    private function setNode(VaultNode $node): void
    {
        $this->selectedFile = $node->id;
        $this->selectedFileExtension = $node->extension;
        $this->selectedFileUrl = new GetUrlFromVaultNode()->handle($node);
        $this->nodeForm->setNode($node);
    }

    private function openFile(VaultNode $node): void
    {
        $this->setNode($node);
        $this->setLastVisitedUrl();

        if ($node->extension === 'md') {
            $this->dispatch('file-render-markup');
        }
    }

    private function setLastVisitedUrl(): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $currentUrl = route('vaults.show', ['vault' => $this->vault->id], false)
            . ($this->selectedFile !== null ? '?file=' . $this->selectedFile : '');
        $currentUser->update([
            'last_visited_url' => $currentUrl,
        ]);
    }
}
