<x-modal wire:model="show">
    <x-modal.panel title="{{ $title }}" top>
        <div
            x-data="searchFile"
            @toggle-mde-search-link-modal.window="openModal('all', $event.detail.url)"
            @toggle-mde-search-image-modal.window="openModal('image', $event.detail.url)"
        >
            <x-tab
                default="internal"
                @mde-insert-link-set-tab.window="selectedTab = $event.detail.tab"
            >
                <x-slot:tabs>
                    <x-tab.button tab="internal">
                        {{ __('Internal') }}
                    </x-tab.button>
                    <x-tab.button tab="external">
                        {{ __('External') }}
                    </x-tab.button>
                </x-slot>
                <x-tab.content tab="internal">
                    <input
                        class="block w-full p-2 border rounded-lg bg-light-base-100 dark:bg-base-800 text-light-base-700 dark:text-base-200 focus:ring-0 focus:outline focus:outline-0 border-light-base-300 dark:border-base-500 focus:border-light-base-600 dark:focus:border-base-400"
                        type="text"
                        placeholder="{{ __('Search') }}"
                        autofocus
                        wire:model.live.debounce.500ms="query"
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
                                            @if ($file['id'] === 0)
                                                <span class="flex gap-2">
                                                    <span
                                                        class="overflow-hidden font-semibold whitespace-nowrap text-ellipsis"
                                                        title="{{ $file['name'] }}"
                                                    >
                                                        {{ $file['name'] }}
                                                    </span>
                                                </span>
                                            @else
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
                                            @endif
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @elseif ($query !== '')
                            <p>{{ __('No results found') }}</p>
                        @endif
                    </div>
                </x-tab.content>
                <x-tab.content tab="external">
                    <x-form class="flex flex-col gap-6" @submit.prevent="insertUrl">
                        <div>
                            <input
                                class="block w-full p-2 border rounded-lg bg-light-base-100 dark:bg-base-800 text-light-base-700 dark:text-base-200 focus:ring-0 focus:outline focus:outline-0 border-light-base-300 dark:border-base-500 focus:border-light-base-600 dark:focus:border-base-400"
                                :class="!errors.externalUrl || 'border-error-500 focus:border-error-700 dark:border-error-500 dark:focus:border-error-700'"
                                type="text"
                                placeholder="{{ __('Type URL') }}"
                                x-model="externalUrl"
                            />
                            <p
                                class="mt-2 text-xs"
                                :class="errors.externalUrl ? 'text-error-500' : 'text-gray-500 dark:text-gray-400'"
                            >
                                {{ __('URLs must start with http(s)://') }}
                            </p>
                        </div>


                        <div class="flex justify-end">
                            <x-form.submit label="{{ __('Save') }}" />
                        </div>
                    </x-form>
                </x-tab.content>
            </x-tab>
        </div>
    </x-modal.panel>
</x-modal>

@script
    <script>
        Alpine.data('searchFile', () => ({
            show: $wire.entangle('show'),
            title: $wire.entangle('title'),
            files: $wire.entangle('files'),
            selectedFile: $wire.entangle('selectedFile'),
            queryType: $wire.entangle('queryType'),
            externalUrl: '',
            errors: {},

            openModal(type, url) {
                if (type === 'image') {
                    this.queryType = 'image';
                    this.title = '{{ __('Insert image') }}';
                } else {
                    this.queryType = 'all';
                    this.title = '{{ __('Insert link') }}';
                }

                if (url.startsWith('http://') || url.startsWith('https://')) {
                    $wire.query = '';
                    this.externalUrl = url;

                    $dispatch('mde-insert-link-set-tab', { tab: 'external' });
                } else {
                    let filename = url.split(/[\/\\]/).pop() || '';
                    const lastDotIndex = filename.lastIndexOf('.');

                    if (lastDotIndex > 0) {
                        filename = filename.substring(0, lastDotIndex);
                    }

                    $wire.query = filename.replaceAll('%20', ' ');
                    this.externalUrl = '';

                    $dispatch('mde-insert-link-set-tab', { tab: 'internal' });
                }

                $refs.modalTitle.innerText = this.title;
                this.show = true;
                $wire.search();
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
                if ($wire.query === '') {
                    const eventName = this.queryType === 'image' ? 'mde-image' : 'mde-link';

                    $dispatch(eventName, { path: '' });
                    $dispatch('close-modal');

                    return;
                }

                if (this.files.length === 0) {
                    return;
                }

                this.insertFileId(this.files[this.selectedFile]['id']);
            },

            insertFileId(id) {
                const eventName = this.queryType === 'image' ? 'mde-image' : 'mde-link';
                const path = `/${this.files[this.selectedFile]['full_path_encoded']}.${this.files[this.selectedFile]['extension']}`;

                $dispatch(eventName, { path: path });
                $dispatch('close-modal');
            },

            insertUrl(event) {
                const eventName = this.queryType === 'image' ? 'mde-image' : 'mde-link';
                const url = event.target.getElementsByTagName('input')[0].value;

                this.errors.externalUrl = !this.validateUrl(url);

                if (url === '' || !this.errors.externalUrl) {
                    $dispatch(eventName, { path: url });
                    $dispatch('close-modal');
                }
            },

            validateUrl(url) {
                try {
                    const objUrl = new URL(url);

                    return objUrl.protocol === 'http:' || objUrl.protocol === 'https:';
                } catch (error) {
                    return false;
                }
            },
        }));
    </script>
@endscript
