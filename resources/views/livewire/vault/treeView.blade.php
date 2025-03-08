<div class="flex flex-grow px-4">
    <x-treeView>
        <div class="sticky top-0 z-[5] flex flex-col gap-2 py-4 bg-light-base-50 dark:bg-base-900">
            <div class="flex justify-center py-1 rounded-sm bg-primary-400 dark:bg-primary-500 text-light-base-50"
                x-show="moving()" x-transition
            >{{ __('Drop item') }}</div>
            <div class="flex justify-between w-full"
                x-data="{ hovered: false }"
                @mouseenter="hovered = true"
                @mouseleave="hovered = false"
            >
                <h3>{{ $vault->name }}</h3>

                <div class="flex items-center" x-show="!moving()"
                    x-transition:enter.duration.300ms
                    x-transition:leave.duration.150ms
                >
                    <x-menu>
                        <x-menu.button>
                            <x-icons.bars3 class="w-5 h-5" />
                        </x-menu.button>

                        <x-menu.items>
                            <x-menu.close>
                                <x-menu.item @click="$wire.dispatchTo('modals.add-node', 'open-modal')">
                                    <x-icons.documentPlus class="w-4 h-4" />
                                    {{ __('New note') }}
                                </x-menu.item>
                                <x-menu.item @click="$wire.dispatchTo('modals.add-node', 'open-modal', { isFile: false })">
                                    <x-icons.folderPlus class="w-4 h-4" />
                                    {{ __('New folder') }}
                                </x-menu.item>
                                <x-menu.item @click="$wire.dispatchTo('modals.import-file', 'open-modal')">
                                    <x-icons.arrowUpTray class="w-4 h-4" />
                                    {{ __('Import file') }}
                                </x-menu.item>
                                <x-modal>
                                    <x-modal.open>
                                        <x-menu.item>
                                            <x-icons.pencilSquare class="w-4 h-4" />
                                            {{ __('Edit vault') }}
                                        </x-menu.item>
                                    </x-modal.open>

                                    <x-modal.panel title="{{ __('Edit vault') }}">
                                        <x-form wire:submit="editVault" class="flex flex-col gap-6">
                                            <x-form.input name="vaultForm.name" label="{{ __('Name') }}"
                                                type="text" required autofocus />

                                            <div class="flex justify-end">
                                                <x-form.submit label="{{ __('Edit') }}" target="edit" />
                                            </div>
                                        </x-form>
                                    </x-modal.panel>
                                </x-modal>
                            </x-menu.close>
                        </x-menu.items>
                    </x-menu>
                </div>

                <a href="" class="flex items-center text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                    x-show="moving() && hovered"
                    x-transition:enter.duration.300ms
                    x-transition:leave.duration.150ms
                    @click.prevent="dropNode(0)"
                >
                    <x-icons.arrowDownOnSquare class="w-5 h-5" />
                </a>
            </div>
        </div>

        <div>
            @if (count($nodes))
                @include('components.vault.treeViewNode', ['nodes' => $nodes, 'root' => true])
            @else
                <p>{{ __('Your vault is empty.') }}</p>
            @endif
        </div>
    </x-treeView>
</div>
