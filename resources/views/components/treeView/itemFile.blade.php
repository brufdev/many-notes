@aware(['node'])

<div class="relative w-full">
    <x-menu>
        <a href="" class="flex items-center w-full gap-2" title="{{ $node->name }}"
            data-id="{{ $node->id }}" x-ref="button"
            @click.prevent="openFile({{ $node->id }})"
            @contextmenu.prevent="menuOpen = !menuOpen"
            @keydown.escape="menuOpen = false"
            @auxclick.outside="menuOpen = false"
        >
            <span class="ml-1 overflow-hidden whitespace-nowrap text-ellipsis">
                {{ $node->name }}
            </span>

            @if (!in_array($node->extension, App\Services\VaultFiles\Note::extensions())) 
                <x-treeView.badge>{{ $node->extension }}</x-treeView.badge>
            @endif
        </a>

        <x-menu.items>
            <x-menu.close>
                <x-menu.item @click="$wire.dispatchTo('modals.edit-node', 'open-modal', { node: {{ $node->id }} })">
                    <x-icons.pencilSquare class="w-4 h-4" />
                    {{ __('Rename') }}
                </x-menu.item>

                <x-menu.item
                    wire:confirm="{{ __('Are you sure you want to delete this file?') }}"
                    wire:click="$parent.deleteNode({{ $node->id }})"
                >
                    <x-icons.trash class="w-4 h-4" />
                    {{ __('Delete') }}
                </x-menu.item>
            </x-menu.close>
        </x-menu.items>
    </x-menu>
</div>
