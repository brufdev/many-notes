<?php

declare(strict_types=1);

namespace App\Livewire\Vault;

use App\Actions\DeleteVaultNode;
use App\Actions\GetUrlFromVaultNode;
use App\Actions\GetVaultNodeFromPath;
use App\Actions\ResolveTwoPaths;
use App\Actions\UpdateVault;
use App\Events\VaultFileSystemUpdated;
use App\Events\VaultNodeDeleted;
use App\Events\VaultNodeUpdated;
use App\Livewire\Forms\VaultNodeForm;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
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
    public ?int $selectedFileId = null;

    #[Locked]
    public ?string $selectedFileExtension = null;

    #[Locked]
    public ?string $selectedFileUrl = null;

    #[Locked]
    public int $selectedFileRefreshes = 0;

    public string $toastErrorMessage = '';

    public function mount(Vault $vault): void
    {
        $this->authorize('view', $vault);
        $this->nodeForm->setVault($this->vault);
        $this->openFileId($this->selectedFileId);
    }

    #[Computed]
    public function selectedFile(): ?VaultNode
    {
        return $this->vault
            ->nodes()
            ->where('id', $this->selectedFileId)
            ->where('is_file', true)
            ->first();
    }

    public function updatedSelectedFileId(): void
    {
        $this->openFileId($this->selectedFileId);
    }

    public function openFileId(?int $fileId = null): void
    {
        $this->reset(['selectedFileRefreshes']);

        if ($fileId === null) {
            $this->reset(['selectedFileId']);
            $this->setLastVisitedUrl();

            return;
        }

        $node = $this->vault
            ->nodes()
            ->where('id', $fileId)
            ->where('is_file', true)
            ->first();

        if ($node === null) {
            $this->reset(['selectedFileId']);
            $errorMessage = __('File not found');
            $this->toastErrorMessage = $errorMessage;
            $this->dispatch('toast', message: $errorMessage, type: 'error');

            return;
        }

        $this->openFile($node);
    }

    public function openFilePath(string $path): void
    {
        /** @var string $currentPath */
        $currentPath = $this->selectedFile instanceof VaultNode
            /** @phpstan-ignore-next-line larastan.noUnnecessaryCollectionCall */
            ? $this->selectedFile->ancestorsAndSelf()->get()->last()->full_path
            : '';
        $resolvedPath = new ResolveTwoPaths()->handle($currentPath, $path);
        $node = new GetVaultNodeFromPath()->handle($this->vault->id, $resolvedPath);

        if (!$node instanceof VaultNode) {
            $this->dispatch('toast', message: __('File not found'), type: 'error');

            return;
        }

        $this->openFile($node);
    }

    #[On('file-refresh')]
    public function refreshFile(int $nodeId): void
    {
        if ($nodeId !== $this->selectedFileId) {
            return;
        }

        $this->setNode($this->selectedFile);
        $this->selectedFileRefreshes++;
    }

    #[On('file-close')]
    public function closeFile(): void
    {
        if (!$this->selectedFile instanceof VaultNode) {
            $this->dispatch('toast', message: __('This file is no longer available'), type: 'error');
        }

        $this->reset(['selectedFileId', 'selectedFileExtension', 'selectedFileUrl', 'selectedFileRefreshes']);
        $this->nodeForm->reset('nodeId');
    }

    public function updated(string $name): void
    {
        if (!str_starts_with($name, 'nodeForm') || !$this->selectedFile instanceof VaultNode) {
            return;
        }

        /** @var VaultNode $node */
        $node = $this->nodeForm->update();
        $this->setNode($node);

        if ($node->wasChanged(['parent_id', 'name'])) {
            /** @var Vault $vault */
            $vault = $node->vault;
            $this->dispatch('node-updated');
            broadcast(new VaultFileSystemUpdated($vault))->toOthers();
        }

        broadcast(new VaultNodeUpdated($node))->toOthers();
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
                    fn (VaultNode $node): bool => $node->id === $this->selectedFileId,
                )
            );

            if ($openFileDeleted) {
                $this->closeFile();
            }

            broadcast(new VaultNodeDeleted($node))->toOthers();
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

    private function setNode(?VaultNode $node): void
    {
        if (!$node instanceof VaultNode) {
            return;
        }

        $this->selectedFileId = $node->id;
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
            . ($this->selectedFileId !== null ? '?file=' . $this->selectedFileId : '');
        $currentUser->update([
            'last_visited_url' => $currentUrl,
        ]);
    }
}
