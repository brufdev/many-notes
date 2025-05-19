<x-modal wire:model="show">
    <x-modal.panel title="{{ __('Search') }}" top>
        <div
            x-data="searchFile"
            @toggle-mde-search-link-modal.window="openModal()"
            @toggle-mde-search-image-modal.window="openModal('image')"
        >
            <input
                class="block w-full p-2 border rounded-lg bg-light-base-100 dark:bg-base-800 text-light-base-700 dark:text-base-200 focus:ring-0 focus:outline focus:outline-0 border-light-base-300 dark:border-base-500 focus:border-light-base-600 dark:focus:border-base-400"
                type="text"
                placeholder="{{ __('Search') }}"
                autofocus
                wire:model.live.debounce.500ms="search"
                @keydown.up.prevent="selectPreviousFile()"
                @keydown.down.prevent="selectNextFile()"
                @keydown.enter.prevent="insertFile()"
            />

            <div class="mt-4">
                @if (count($files))
                    <ul class="flex flex-col gap-2" wire:loading.class="opacity-50">
                        @foreach ($files as $index => $file)
                            <li
                                class="p-2 rounded-lg"
                                :class="
                                    selectedFile === {{ $index }}
                                    ? 'bg-light-base-300 dark:bg-base-800 text-light-base-950 dark:text-base-50'
                                    : 'text-light-base-700 dark:text-base-200'
                                "
                                @mouseenter="selectFile({{ $index }})"
                                wire:key="mde-search-{{ $file['id'] }}"
                            >
                                <button
                                    class="flex flex-col w-full gap-2 py-1 text-left"
                                    type="button"
                                    @click="insertFile({{ $file['id'] }})"
                                >
                                    <span class="flex gap-2">
                                        <span
                                            class="overflow-hidden font-semibold whitespace-nowrap text-ellipsis"
                                            title="{{ $file['name'] }}"
                                        >
                                            {{ $file['name'] }}
                                        </span>

                                        @if ($file['extension'] !== 'md')
                                            <x-treeView.badge>{{ $file['extension'] }}</x-treeView.badge>
                                        @endif
                                    </span>
                                    @if ($file['dir_name'] !== '')
                                        <span
                                            class="overflow-hidden text-xs whitespace-nowrap text-ellipsis"
                                            title="{{ $file['full_path'] }}"
                                        >
                                            {{ $file['dir_name'] }}
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
        Alpine.data('searchFile', () => ({
            show: $wire.entangle('show'),
            files: $wire.entangle('files'),
            selectedFile: $wire.entangle('selectedFile'),
            searchType: $wire.entangle('searchType'),

            openModal(searchType) {
                this.searchType = searchType === 'image' ? 'image' : 'all';
                this.show = !this.show;
            },

            selectPreviousFile() {
                const file = this.selectedFile === 0 ? this.files.length - 1 : this.selectedFile - 1;
                this.selectFile(file);
                this.ensureFileIsVisible();
            },

            selectNextFile() {
                const file = this.selectedFile === this.files.length - 1 ? 0 : this.selectedFile + 1;
                this.selectFile(file);
                this.ensureFileIsVisible();
            },

            selectFile(index) {
                if (index < 0 || index > this.files.length - 1) {
                    return;
                }

                this.selectedFile = index;
            },

            ensureFileIsVisible() {
                if (this.files.length === 0) {
                    return;
                }

                const modalElement = this.$root.offsetParent.offsetParent;
                const fileElement = modalElement.getElementsByTagName('li')[this.selectedFile];
                const fileRect = fileElement.getBoundingClientRect();

                if (this.selectedFile === 0) {
                    modalElement.scroll({
                        top: 0,
                        behavior: 'smooth',
                    });

                    return;
                }

                if (this.selectedFile === this.files.length - 1) {
                    modalElement.scroll({
                        top: modalElement.scrollHeight - modalElement.clientHeight,
                        behavior: 'smooth',
                    });

                    return;
                }

                if (this.isFileVisible(modalElement.clientHeight, fileRect.top, fileRect.bottom)) {
                    return;
                }

                if (fileRect.top < 0) {
                    modalElement.scroll({
                        top: modalElement.scrollTop + fileRect.top,
                        behavior: 'smooth',
                    });
                } else {
                    modalElement.scroll({
                        top: modalElement.scrollTop + fileRect.bottom - modalElement.clientHeight,
                        behavior: 'smooth',
                    });
                }
            },

            isFileVisible(containerHeight, elementTop, elementBottom) {
                return elementTop >= 0
                    && elementTop <= containerHeight
                    && elementBottom >= 0
                    && elementBottom <= containerHeight;
            },

            insertFile() {
                if (this.files.length === 0) {
                    return;
                }

                this.insertFileId(this.files[this.selectedFile]['id']);
            },

            insertFileId(id) {
                const eventName = this.searchType === 'image' ? 'mde-image' : 'mde-link';
                const fileName = this.files[this.selectedFile]['name'];
                const filePath = '/' + this.files[this.selectedFile]['full_path_encoded'] + '.' + this.files[this.selectedFile]['extension'];

                $dispatch(eventName, { name: fileName, path: filePath });
                $dispatch('close-modal');
            },
        }));
    </script>
@endscript
