@aware(['node']) 

<div class="relative w-full">
    <x-menu>
        <a href="" class="flex items-center w-full" title="{{ $node->name }}"
            data-id="{{ $node->id }}" x-ref="button"
            @click.prevent="accordionOpen = !accordionOpen"
            @contextmenu.prevent="menuOpen = !menuOpen"
            @keydown.escape="menuOpen = false"
            @auxclick.outside="menuOpen = false"
        >
            <x-icons.chevronRight x-show="!accordionOpen" class="w-4 h-4" />
            <x-icons.chevronDown x-show="accordionOpen" class="w-4 h-4" x-cloak />

            <span class="ml-1 overflow-hidden whitespace-nowrap text-ellipsis">
                {{ $node->name }}
            </span>
        </a>

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

                <x-menu.item
                    @click="$wire.dispatchTo('modals.import-file', 'open-modal', { parent: {{ $node->id }} })">
                    <x-icons.arrowUpTray class="w-4 h-4" />
                    {{ __('Import file') }}
                </x-menu.item>

                <x-menu.item @click="$wire.dispatchTo('modals.edit-node', 'open-modal', { node: {{ $node->id }} })">
                    <x-icons.pencilSquare class="w-4 h-4" />
                    {{ __('Rename') }}
                </x-menu.item>

                <x-menu.item wire:click="$parent.setTemplateFolder({{ $node->id }})" title="{{ __('Set as template folder') }}">
                    <x-icons.documentDuplicate class="w-4 h-4" />
                    {{ __('Template folder') }}
                </x-menu.item>

                <x-menu.item wire:confirm="{{ __('Are you sure you want to delete this folder?') }}"
                    wire:click="$parent.deleteNode({{ $node->id }})">
                    <x-icons.trash class="w-4 h-4" />
                    {{ __('Delete') }}
                </x-menu.item>
            </x-menu.close>
        </x-menu.items>
    </x-menu>
</div>
