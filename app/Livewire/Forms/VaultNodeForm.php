<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Actions\CreateVaultNode;
use App\Actions\UpdateVaultNode;
use App\Events\VaultFileSystemUpdatedEvent;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class VaultNodeForm extends Form
{
    #[Locked]
    public int $vaultId;

    #[Locked]
    public ?int $nodeId = null;

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
                'min:1',
                // One or more allowed characters, not starting with a dot or space
                'regex:/^(?![. ])[\w\s.,;_\-&%#\[\]()=]+$/u',
                Rule::unique(VaultNode::class)
                    ->where('vault_id', $this->vaultId)
                    ->where('parent_id', $this->parent_id)
                    ->where('extension', $this->extension)
                    ->ignore($this->nodeId),
            ],
        ];
    }

    public function setVault(int $vaultId): void
    {
        $this->vaultId = $vaultId;
    }

    public function setNode(VaultNode $node): void
    {
        $this->nodeId = $node->id;
        $this->parent_id = $node->parent_id;
        $this->is_file = (bool) $node->is_file;
        $this->name = $node->name;
        $this->extension = $node->extension;
        $this->content = $node->content;
    }

    public function create(): VaultNode
    {
        $this->validate();

        /** @var Vault $vault */
        $vault = Vault::find($this->vaultId);

        $node = new CreateVaultNode()->handle($vault, [
            'parent_id' => $this->parent_id,
            'is_file' => $this->is_file,
            'name' => $this->name,
            'extension' => $this->is_file ? 'md' : null,
            'content' => $this->content,
        ]);

        $this->reset(['name']);

        return $node;
    }

    public function update(bool $broadcastToOthers = false): ?VaultNode
    {
        $node = VaultNode::find($this->nodeId);

        if ($node === null) {
            return null;
        }

        $this->validate();

        $node = new UpdateVaultNode()->handle(
            $node,
            [
                'parent_id' => $this->parent_id,
                'name' => $this->name,
                'content' => $this->content,
            ],
            $broadcastToOthers,
        );

        if ($node->wasChanged(['parent_id', 'name'])) {
            /** @var Vault $vault */
            $vault = $node->vault;

            broadcast(new VaultFileSystemUpdatedEvent($vault));
        }

        return $node;
    }
}
