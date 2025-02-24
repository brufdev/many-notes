<?php

declare(strict_types=1);

namespace App\Livewire\Vault;

use App\Actions\UpdateVaultNode;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Builder;

#[On('node-updated')]
final class TreeView extends Component
{
    public Vault $vault;

    public function moveNode(VaultNode $source, ?VaultNode $target): void
    {
        $this->authorize('view', $source->vault);

        /** @var Vault $sourceVault */
        $sourceVault = $source->vault;

        /** @var VaultNode $target */
        if ($target->exists && !$sourceVault->is($target->vault)) {
            abort(403);
        }

        $parentId = null;

        if ($target->exists) {
            // Ignore if $target is the same as $source or if it is a child of $source
            // @phpstan-ignore-next-line larastan.noUnnecessaryCollectionCall
            if ($target->ancestorsAndSelf()->pluck('id')->contains($source->id)) {
                return;
            }

            $parentId = $target->is_file ? $target->parent_id : $target->id;
        }

        if ($source->parent_id === $parentId) {
            return;
        }

        new UpdateVaultNode()->handle($source, ['parent_id' => $parentId]);
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="fixed inset-0 z-40 opacity-50 bg-light-base-200 dark:bg-base-950">
            <div class="flex items-center justify-center h-full">
                <x-icons.spinner class="w-5 h-5 animate-spin" />
            </div>
        </div>
        HTML;
    }

    public function render(): Factory|View
    {
        $constraint = function (Builder $query): void {
            $query->whereNull('parent_id')->where('vault_id', $this->vault->id);
        };

        $nodes = VaultNode::treeOf($constraint)->orderBy('is_file')->orderBy('name')->get()->toTree();

        return view('livewire.vault.treeView', [
            'nodes' => $nodes,
        ]);
    }
}
