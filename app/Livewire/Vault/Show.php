<?php

declare(strict_types=1);

namespace App\Livewire\Vault;

use App\Actions\DeleteVaultNode;
use App\Actions\GetUrlFromVaultNode;
use App\Actions\GetVaultNodeFromPath;
use App\Actions\ResolveTwoPaths;
use App\Actions\UpdateVault;
use App\Events\VaultFileSystemUpdatedEvent;
use App\Events\VaultNodeDeletedEvent;
use App\Livewire\Forms\VaultNodeForm;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Throwable;

final class Show extends Component
{
    public VaultNodeForm $nodeForm;

    #[Locked]
    public int $vaultId;

    #[Url(as: 'file', history: true)]
    public ?int $selectedFileId = null;

    #[Locked]
    public ?string $selectedFileExtension = null;

    #[Locked]
    public ?string $selectedFileUrl = null;

    #[Locked]
    public string $toastErrorMessage = '';

    public function mount(): void
    {
        $this->checkPermission();
        $this->nodeForm->setVault($this->vaultId);
        $this->openFileId($this->selectedFileId);
    }

    #[Computed]
    public function vault(): Vault
    {
        return Vault::findOrFail($this->vaultId);
    }

    #[Computed]
    public function selectedFile(): ?VaultNode
    {
        if ($this->vault === null) {
            return null;
        }

        return $this->vault
            ->nodes()
            ->where('id', $this->selectedFileId)
            ->where('is_file', true)
            ->first();
    }

    public function checkPermission(): void
    {
        try {
            $this->authorize('view', $this->vault);
        } catch (AuthorizationException) {
            session()->flash('error', __('This vault is no longer available'));
            $this->redirect(route('vaults.index'));
        } catch (ModelNotFoundException) { /** @phpstan-ignore catch.neverThrown */
            session()->flash('error', __('Vault not found'));
            $this->redirect(route('vaults.index'));
        }
    }

    public function updatedSelectedFileId(): void
    {
        $this->openFileId($this->selectedFileId);
    }

    public function openFileId(?int $fileId = null): void
    {
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

            $this->toastErrorMessage = __('File not found');
            $this->dispatch('toast', message: $this->toastErrorMessage, type: 'error');

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

    public function refreshFile(int $nodeId): void
    {
        if ($nodeId !== $this->selectedFileId) {
            return;
        }

        $this->setNode($this->selectedFile);

        $this->dispatch('file-refreshed');
    }

    #[On('file-close')]
    public function closeFile(): void
    {
        if (!$this->selectedFile instanceof VaultNode) {
            $this->dispatch('toast', message: __('This file is no longer available'), type: 'error');
        }

        $this->reset(['selectedFileId', 'selectedFileExtension', 'selectedFileUrl']);
        $this->nodeForm->reset('nodeId');
        unset($this->selectedFile);
    }

    #[Renderless]
    public function updated(string $name): void
    {
        if (!str_starts_with($name, 'nodeForm') || !$this->selectedFile instanceof VaultNode) {
            return;
        }

        /** @var VaultNode $node */
        $node = $this->nodeForm->update();
        $this->setNode($node);
    }

    #[Renderless]
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

    #[Renderless]
    public function deleteNode(VaultNode $node): void
    {
        $this->authorize('delete', $node);

        try {
            $deletedNodes = new DeleteVaultNode()->handle($node);
            $openFileDeleted = !is_null(
                array_find(
                    $deletedNodes,
                    fn(VaultNode $node): bool => $node->id === $this->selectedFileId,
                )
            );

            if ($openFileDeleted) {
                $this->closeFile();
            }

            $message = $node->is_file ? __('File deleted') : __('Folder deleted');
            $this->dispatch('toast', message: $message, type: 'success');

            broadcast(new VaultFileSystemUpdatedEvent($this->vault));
            broadcast(new VaultNodeDeletedEvent($node))->toOthers();
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
        unset($this->selectedFile);
    }

    private function openFile(VaultNode $node): void
    {
        $this->setNode($node);
        $this->setLastVisitedUrl();

        $this->dispatch('file-opened');
    }

    private function setLastVisitedUrl(): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $currentUrl = route('vaults.show', ['vaultId' => $this->vaultId], false)
            . ($this->selectedFileId !== null ? '?file=' . $this->selectedFileId : '');
        $currentUser->update([
            'last_visited_url' => $currentUrl,
        ]);
    }
}
