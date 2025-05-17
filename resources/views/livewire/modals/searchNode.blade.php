<x-modal wire:model="show">
    <x-modal.panel title="{{ __('Search') }}" top>
        <div
            x-data="searchNode"
            @toggle-search-modal.window="toggleModal()"
        >
            <input
                class="block w-full p-2 border rounded-lg bg-light-base-100 dark:bg-base-800 text-light-base-700 dark:text-base-200 focus:ring-0 focus:outline focus:outline-0 border-light-base-300 dark:border-base-500 focus:border-light-base-600 dark:focus:border-base-400"
                type="text"
                placeholder="{{ __('Search') }}"
                autofocus
                wire:model.live.debounce.500ms="search"
                @keydown.up.prevent="selectPreviousNode()"
                @keydown.down.prevent="selectNextNode()"
                @keydown.enter.prevent="openFile()"
            />

            <div class="mt-4">
                @if (count($nodes))
                    <ul class="flex flex-col gap-2" wire:loading.class="opacity-50">
                        @foreach ($nodes as $index => $node)
                            <li
                                class="p-2 rounded-lg"
                                :class="
                                    selectedNode === {{ $index }}
                                    ? 'bg-light-base-300 dark:bg-base-800 text-light-base-950 dark:text-base-50'
                                    : 'text-light-base-700 dark:text-base-200'
                                "
                                @mouseenter="selectNode({{ $index }})"
                                wire:key="search-{{$node['id'] }}"
                            >
                                <button
                                    class="flex flex-col w-full gap-2 pb-1 text-left"
                                    type="button"
                                    @click="openFileId({{ $node['id'] }})"
                                >
                                    <span class="flex gap-2">
                                        <span
                                            class="overflow-hidden font-semibold whitespace-nowrap text-ellipsis"
                                            title="{{ $node['name'] }}"
                                        >
                                            {{ $node['name'] }}
                                        </span>

                                        @if ($node['extension'] !== 'md')
                                            <x-treeView.badge>{{ $node['extension'] }}</x-treeView.badge>
                                        @endif
                                    </span>
                                    @if ($node['dir_name'] !== '')
                                        <span
                                            class="overflow-hidden text-xs whitespace-nowrap text-ellipsis"
                                            title="{{ $node['full_path'] }}"
                                        >
                                            {{ $node['dir_name'] }}
                                        </span>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @elseif ($search !== '')
                    <p>{{ __('No results found') }}</p>
                @endif
            </div>
        </div>
    </x-modal.panel>
</x-modal>

@script
    <script>
        Alpine.data('searchNode', () => ({
            show: $wire.entangle('show'),
            nodes: $wire.entangle('nodes'),
            selectedNode: $wire.entangle('selectedNode'),

            toggleModal() {
                this.show = !this.show;
            },

            selectPreviousNode() {
                const node = this.selectedNode === 0 ? this.nodes.length - 1 : this.selectedNode - 1;
                this.selectNode(node);
                this.ensureNodeIsVisible();
            },

            selectNextNode() {
                const node = this.selectedNode === this.nodes.length - 1 ? 0 : this.selectedNode + 1;
                this.selectNode(node);
                this.ensureNodeIsVisible();
            },

            selectNode(index) {
                if (index < 0 || index > this.nodes.length - 1) {
                    return;
                }

                this.selectedNode = index;
            },

            ensureNodeIsVisible() {
                if (this.nodes.length === 0) {
                    return;
                }

                const modalElement = this.$root.offsetParent.offsetParent;
                const nodeElement = modalElement.getElementsByTagName('li')[this.selectedNode];
                const nodeRect = nodeElement.getBoundingClientRect();

                if (this.selectedNode === 0) {
                    modalElement.scroll({
                        top: 0,
                        behavior: 'smooth',
                    });

                    return;
                }

                if (this.selectedNode === this.nodes.length - 1) {
                    modalElement.scroll({
                        top: modalElement.scrollHeight - modalElement.clientHeight,
                        behavior: 'smooth',
                    });

                    return;
                }

                if (this.isNodeVisible(modalElement.clientHeight, nodeRect.top, nodeRect.bottom)) {
                    return;
                }

                if (nodeRect.top < 0) {
                    modalElement.scroll({
                        top: modalElement.scrollTop + nodeRect.top,
                        behavior: 'smooth',
                    });
                } else {
                    modalElement.scroll({
                        top: modalElement.scrollTop + nodeRect.bottom - modalElement.clientHeight,
                        behavior: 'smooth',
                    });
                }
            },

            isNodeVisible(containerHeight, elementTop, elementBottom) {
                return elementTop >= 0
                    && elementTop <= containerHeight
                    && elementBottom >= 0
                    && elementBottom <= containerHeight;
            },

            openFile() {
                if (this.nodes.length === 0) {
                    return;
                }

                this.openFileId(this.nodes[this.selectedNode]['id']);
            },

            openFileId(id) {
                $wire.$parent.openFileId(id);
                $dispatch('close-modal');
            },
        }));
    </script>
@endscript
