@props(['node'])

<div class="relative w-full">
    <x-menu>
        <button x-ref="button" @click="accordionOpen = !accordionOpen" @contextmenu.prevent="menuOpen = !menuOpen"
            @keydown.escape="menuOpen = false" @auxclick.outside="menuOpen = false" class="flex items-center w-full">
            <span class="flex items-center w-full">
                <x-icons.chevronRight x-show="!accordionOpen" class="w-4 h-4" />
                <x-icons.chevronDown x-show="accordionOpen" class="w-4 h-4" x-cloak />

                <span title="{{ $node->name }}" class="ml-1 overflow-hidden whitespace-nowrap text-ellipsis">
                    {{ $node->name }}
                </span>
            </span>
        </button>

        <x-menu.items>
            <x-menu.close>
                <x-menu.item
                    @click="$wire.dispatchTo('modals.add-node', 'open-modal', { parent: {{ $node->id }} })">
                    <x-icons.documentPlus class="w-4 h-4" />

                    {{ __('New note') }}
                </x-menu.item>

                <x-menu.item
                    @click="$wire.dispatchTo('modals.add-node', 'open-modal', { parent: {{ $node->id }}, isFile: false })">
                    <x-icons.folderPlus class="w-4 h-4" />
                    {{ __('New folder') }}
                </x-menu.item>

                <x-menu.item @click="$wire.dispatchTo('modals.edit-node', 'open-modal', { node: {{ $node->id }} })">
                    <x-icons.pencilSquare class="w-4 h-4" />
                    {{ __('Rename') }}
                </x-menu.item>

                <x-menu.item wire:confirm="{{ __('Are you sure you want to delete this folder?') }}"
                    wire:click="deleteNode({{ $node->id }})">
                    <x-icons.trash class="w-4 h-4" />
                    {{ __('Delete') }}
                </x-menu.item>
            </x-menu.close>
        </x-menu.items>
    </x-menu>
</div>