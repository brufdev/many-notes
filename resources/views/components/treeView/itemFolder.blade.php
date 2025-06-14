@aware(['node']) 

<div
    class="relative flex items-center w-full"
    x-data="{ hovered: false }"
    @mouseenter="hovered = true"
    @mouseleave="hovered = false"
>
    <x-menu class="flex flex-grow">
        <a
            href=""
            class="flex items-center w-full"
            title="{{ $node->name }}"
            x-ref="button"
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
                <x-menu.item @click="$wire.dispatchTo('modals.add-node', 'open-modal', { parent: {{ $node->id }} })">
                    <x-icons.documentPlus class="w-4 h-4" />

                    {{ __('New note') }}
                </x-menu.item>

                <x-menu.item @click="$wire.dispatchTo('modals.add-node', 'open-modal', { parent: {{ $node->id }}, isFile: false })">
                    <x-icons.folderPlus class="w-4 h-4" />
                    {{ __('New folder') }}
                </x-menu.item>

                <x-menu.item @click="$wire.dispatchTo('modals.import-file', 'open-modal', { parent: {{ $node->id }} })">
                    <x-icons.arrowUpTray class="w-4 h-4" />
                    {{ __('Import file') }}
                </x-menu.item>

                <x-menu.item @click="$wire.dispatchTo('modals.edit-node', 'open-modal', { node: {{ $node->id }} })">
                    <x-icons.pencilSquare class="w-4 h-4" />
                    {{ __('Rename') }}
                </x-menu.item>

                <x-menu.item @click="moveNode({{ $node->id }})">
                    <x-icons.arrowUpOnSquare class="w-4 h-4" />
                    {{ __('Move') }}
                </x-menu.item>

                <x-menu.item title="{{ __('Set as template folder') }}" wire:click="$parent.setTemplateFolder({{ $node->id }})">
                    <x-icons.documentDuplicate class="w-4 h-4" />
                    {{ __('Template folder') }}
                </x-menu.item>

                <x-menu.item
                    wire:confirm="{{ __('Are you sure you want to delete this folder?') }}"
                    wire:click="$parent.deleteNode({{ $node->id }})"
                >
                    <x-icons.trash class="w-4 h-4" />
                    {{ __('Delete') }}
                </x-menu.item>
            </x-menu.close>
        </x-menu.items>
    </x-menu>

    <a
        href=""
        class="flex items-center text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
        x-show="moving() && hovered && showDropZone(['{{ str_replace(".", "','", $node->path) }}'])"
        x-transition:enter.duration.300ms
        x-transition:leave.duration.150ms
        @click.prevent="dropNode({{ $node->id }})"
    >
        <x-icons.arrowDownOnSquare class="w-5 h-5" />
    </a>
</div>
