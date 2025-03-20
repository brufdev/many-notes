<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Actions\CreateVaultNode;
use App\Actions\ProcessVaultNodeLinks;
use App\Actions\ProcessVaultNodeTags;
use App\Actions\UpdateVaultNode;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class VaultNodeForm extends Form
{
    public Vault $vault;

    public ?VaultNode $node = null;

    public ?int $parent_id = null;

    public bool $is_file = true;

    #[Validate]
    public string $name = '';

    public ?string $extension = null;

    public ?string $content = null;

    /**
     * @return array<string, list<string|Unique>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'min:3',
                'regex:/^[\w]+[\s\w\&.-]+$/u',
                Rule::unique(VaultNode::class)
                    ->where('vault_id', $this->vault->id)
                    ->where('parent_id', $this->parent_id)
                    ->where('extension', $this->extension)
                    ->ignore($this->node),
            ],
        ];
    }

    public function setVault(Vault $vault): void
    {
        $this->vault = $vault;
    }

    public function setNode(VaultNode $node): void
    {
        $this->node = $node;
        $this->parent_id = $node->parent_id;
        $this->is_file = (bool) $node->is_file;
        $this->name = $node->name;
        $this->extension = $node->extension;
        $this->content = $node->content;
    }

    public function create(): VaultNode
    {
        $this->name = mb_ltrim($this->name);
        $this->validate();
        $node = new CreateVaultNode()->handle($this->vault, [
            'parent_id' => $this->parent_id,
            'is_file' => $this->is_file,
            'name' => $this->name,
            'extension' => $this->is_file ? 'md' : null,
            'content' => $this->content,
        ]);

        if ($node->is_file && $node->extension === 'md') {
            new ProcessVaultNodeLinks()->handle($node);
            new ProcessVaultNodeTags()->handle($node);
        }

        $this->reset(['name']);

        return $node;
    }

    public function update(): void
    {
        if (is_null($this->node)) {
            return;
        }

        $this->name = mb_ltrim($this->name);
        $this->validate();

        new UpdateVaultNode()->handle($this->node, [
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'content' => $this->content,
        ]);

        if ($this->node->is_file && $this->node->extension === 'md') {
            new ProcessVaultNodeLinks()->handle($this->node);
            new ProcessVaultNodeTags()->handle($this->node);
        }
    }
}
