<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Events\VaultNodeUpdatedEvent;
use App\Models\Vault;
use App\Models\VaultNode;
use App\Services\VaultFiles\Note;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Collection;

final class MarkdownEditorTemplate extends Component
{
    use Modal;

    public Vault $vault;

    public ?VaultNode $selectedFile = null;

    /** @var Collection<int, VaultNode> */
    public ?Collection $templates = null;

    public function mount(Vault $vault): void
    {
        $this->authorize('view', $vault);
    }

    #[On('open-modal')]
    public function open(VaultNode $selectedFile): void
    {
        $this->openModal();
        $this->getTemplates();
        $this->selectedFile = $selectedFile;
    }

    public function insertTemplate(VaultNode $node): void
    {
        $this->authorize('update', $this->vault);
        $sameVault = $node->vault && $node->vault->is($this->vault);
        $isNote = $node->is_file && in_array($node->extension, Note::extensions());
        $isTemplate = $node->parent_id && $node->parent_id === $this->vault->templates_node_id;
        $fileSelected = $this->selectedFile && $this->selectedFile->exists();

        if (!$sameVault || !$isNote || !$isTemplate || !$fileSelected) {
            $this->dispatch('toast', message: __('Something went wrong'), type: 'error');

            return;
        }

        /** @var VaultNode $selectedFile */
        $selectedFile = $this->selectedFile;
        $now = now();
        $content = str_replace(
            ['{{date}}', '{{time}}'],
            [$now->format('Y-m-d'), $now->format('H:i')],
            (string) $node->content,
        );
        $content = str_contains($content, '{{content}}')
            ? str_replace('{{content}}', (string) $selectedFile->content, $content)
            : $content . PHP_EOL . $selectedFile->content;
        $selectedFile->update(['content' => $content]);

        $this->dispatch('toast', message: __('Template inserted'), type: 'success');

        broadcast(new VaultNodeUpdatedEvent($selectedFile));
    }

    public function render(): Factory|View
    {
        return view('livewire.modals.markdownEditorTemplate');
    }

    private function getTemplates(): void
    {
        if (!$this->vault->templatesNode) {
            $this->templates = null;

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
}
