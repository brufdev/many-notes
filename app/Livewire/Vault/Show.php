<?php

declare(strict_types=1);

namespace App\Livewire\Vault;

use App\Actions\DeleteVaultNode;
use App\Actions\GetUrlFromVaultNode;
use App\Actions\GetVaultNodeFromPath;
use App\Actions\ResolveTwoPaths;
use App\Actions\UpdateVault;
use App\Livewire\Forms\VaultNodeForm;
use App\Models\Vault;
use App\Models\VaultNode;
use App\Services\VaultFiles\Note;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Throwable;

final class Show extends Component
{
    public Vault $vault;

    public VaultNodeForm $nodeForm;

    /** @var Collection<int, VaultNode> */
    public Collection $templates;

    #[Locked]
    #[Url(as: 'file')]
    public ?int $selectedFile = null;

    #[Locked]
    public ?string $selectedFileExtension = null;

    #[Locked]
    public ?string $selectedFileUrl = null;

    public bool $isEditMode = true;

    public function mount(Vault $vault): void
    {
        $this->authorize('view', $vault);
        new UpdateVault()->handle($vault, [
            'opened_at' => now(),
        ]);
        $this->vault = $vault;
        $this->nodeForm->setVault($this->vault);
        $this->getTemplates();

        if ((int) $this->selectedFile > 0) {
            $selectedFile = $this->vault->nodes()
                ->where('id', $this->selectedFile)
                ->where('is_file', true)
                ->first();

            if (!$selectedFile) {
                $this->selectedFile = null;

                return;
            }

            $this->openFile($selectedFile);
        }
    }

    public function openFile(VaultNode $node): void
    {
        $this->authorize('view', $node->vault);

        if (!$node->vault || !$node->vault->is($this->vault) || !$node->is_file) {
            $this->selectedFile = null;

            return;
        }

        $this->setNode($node);

        if ($node->extension === 'md') {
            $this->dispatch('file-render-markup');
        }
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
        $this->authorize('view', $node->vault);

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

            if ($node->parent_id === $this->vault->templates_node_id) {
                $this->getTemplates();
            }
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
        $this->getTemplates();
        $this->dispatch('toast', message: __('Template folder updated'), type: 'success');
    }

    public function insertTemplate(VaultNode $node): void
    {
        $this->authorize('update', $this->vault);
        $sameVault = $node->vault && $this->vault->id === $node->vault->id;
        $isNote = $node->is_file && in_array($node->extension, Note::extensions());
        $isTemplate = $node->parent_id === $this->vault->templates_node_id;
        $fileSelected = (int) $this->selectedFile > 0;

        if (!$sameVault || !$isNote || !$isTemplate || !$fileSelected || !$this->isEditMode) {
            $this->dispatch('toast', message: __('Something went wrong'), type: 'error');

            return;
        }

        $now = now();
        /** @var VaultNode $selectedNode */
        $selectedNode = $this->nodeForm->node;
        $content = str_replace(
            ['{{date}}', '{{time}}'],
            [$now->format('Y-m-d'), $now->format('H:i')],
            (string) $node->content,
        );
        $content = str_contains($content, '{{content}}')
            ? str_replace('{{content}}', (string) $selectedNode->content, $content)
            : $content . PHP_EOL . $selectedNode->content;
        $selectedNode->update(['content' => $content]);
        $this->nodeForm->setNode($selectedNode);
        $this->dispatch('toast', message: __('Template inserted'), type: 'success');
    }

    #[On('templates-refresh')]
    public function getTemplates(): void
    {
        if (!$this->vault->templatesNode) {
            return;
        }

        $this->templates = $this->vault
            ->templatesNode
            ->childs()
            ->where('is_file', true)
            ->where('extension', 'LIKE', 'md')
            ->orderBy('name')
            ->get();
    }

    public function deleteNode(VaultNode $node): void
    {
        $this->authorize('delete', $node->vault);

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

            $templateDeleted = !is_null(
                array_find(
                    $deletedNodes,
                    fn (VaultNode $node): bool => $node->parent_id === $this->vault->templates_node_id,
                )
            );
            if ($templateDeleted) {
                $this->getTemplates();
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
}
