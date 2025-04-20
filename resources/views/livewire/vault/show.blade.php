<div class="flex flex-col h-dvh"
    x-data="vault"
    @file-render-markup.window="$nextTick(() => { markdownToHtml() })"
>
    <x-layouts.appHeader>
        <div class="flex items-center gap-4">
            <button type="button" class="hover:text-light-base-950 dark:hover:text-base-50"
                @click="toggleLeftPanel"
            >
                <x-icons.bars3BottomLeft class="w-5 h-5" />
            </button>
            <button type="button" class="hover:text-light-base-950 dark:hover:text-base-50"
                @click="$wire.dispatchTo('modals.search-node', 'open-modal')"
            >
                <x-icons.magnifyingGlass class="w-5 h-5" />
            </button>
        </div>

        <div class="flex items-center gap-4">
            <livewire:layout.notification-menu />
            <livewire:layout.user-menu />
            <button type="button" class="hover:text-light-base-950 dark:hover:text-base-50"
                @click="toggleRightPanel"
            >
                <x-icons.bars3BottomRight class="w-5 h-5" />
            </button>
        </div>
    </x-layouts.appHeader>

    <x-layouts.appMain>
        <div class="relative flex w-full" x-cloak>
            <div class="fixed inset-0 z-40 opacity-50 bg-light-base-200 dark:bg-base-950"
                wire:loading wire:target.except="nodeForm.name, nodeForm.content"
            >
                <div class="flex items-center justify-center h-full">
                    <x-icons.spinner class="w-5 h-5 animate-spin" />
                </div>
            </div>
            <div class="fixed inset-0 z-20 opacity-50 bg-light-base-200 dark:bg-base-950"
                x-show="(isLeftPanelOpen || isRightPanelOpen) && isSmallDevice" @click="closePanels"
                x-transition:enter="ease-out duration-300" x-transition:leave="ease-in duration-200"
            ></div>
            <div class="absolute top-0 left-0 z-30 flex flex-col h-full overflow-hidden overflow-y-auto transition-all w-60 bg-light-base-50 dark:bg-base-900"
                :class="{ 'translate-x-0': isLeftPanelOpen, '-translate-x-full hidden': !isLeftPanelOpen }"
            >
                <livewire:vault.tree-view lazy="on-load" :vault="$this->vault" />
            </div>

            <div class="absolute top-0 bottom-0 right-0 flex flex-col w-full overflow-y-auto transition-all text-start bg-light-base-200 dark:bg-base-950"
                :class="{ 'md:pl-60': isLeftPanelOpen, 'md:pr-60': isRightPanelOpen }" id="nodeContainer"
            >
                <div class="flex flex-col h-full w-full max-w-[48rem] mx-auto p-4">
                    <div class="flex flex-col w-full h-full gap-4" x-show="$wire.selectedFileId">
                        <div class="z-[5]">
                            <div class="flex justify-between">
                                <input type="text" wire:model.live.debounce.500ms="nodeForm.name"
                                    class="flex flex-grow p-0 px-1 text-lg bg-transparent border-0 focus:ring-0 focus:outline-0" />

                                <div class="flex items-center gap-2">
                                    <span class="flex items-center" wire:loading.flex wire:target="nodeForm.name, nodeForm.content">
                                        <x-icons.spinner class="w-4 h-4 animate-spin" />
                                    </span>
                                    <div x-show="users.length > 1">
                                        <x-menu class="flex">
                                            <button x-ref="button"
                                                @mouseenter="menuOpen = true"
                                                @mouseleave="menuOpen = false"
                                            >
                                                <x-icons.userGroup class="w-[1.1rem] h-[1.1rem]" />
                                                <span class="absolute bottom-0 right-0 w-1.5 h-1.5 rounded-full border bg-success-500 border-light-base-200 dark:border-base-950"></span>
                                            </button>
                    
                                            <x-menu.items>
                                                <div class="px-3">
                                                    {{ __('Users in this file') }}
                                                </div>
                                                <x-menu.itemDivider></x-menu.itemDivider>
                                                <template x-for="user in users">
                                                    <x-menu.item x-text="user.name"></x-menu.item>
                                                </template>
                                            </x-menu.items>
                                        </x-menu>
                                    </div>
                                    <button title="{{ __('Close file') }}" wire:click="closeFile">
                                        <x-icons.xMark class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>

                            @error('nodeForm.name')
                                <p class="text-sm text-error-500" aria-live="assertive">{{ $message }}</p>
                            @enderror
                        </div>
                        @if (in_array($nodeForm->extension, App\Services\VaultFiles\Note::extensions()))
                            <x-markdownEditor />
                        @elseif (in_array($nodeForm->extension, App\Services\VaultFiles\Image::extensions()))
                            <div>
                                <img src="{{ $selectedFileUrl }}" />
                            </div>
                        @elseif (in_array($nodeForm->extension, App\Services\VaultFiles\Pdf::extensions()))
                            <object type="application/pdf" data="{{ $selectedFileUrl }}"
                                class="w-full h-full"></object>
                        @elseif (in_array($nodeForm->extension, App\Services\VaultFiles\Video::extensions()))
                            <video class="w-full" controls>
                                <source src="{{ $selectedFileUrl }}" />
                                {{ __('Your browser does not support the video tag') }}
                            </video>
                        @elseif (in_array($nodeForm->extension, App\Services\VaultFiles\Audio::extensions()))
                            <div class="flex items-start justify-center w-full">
                                <audio class="w-full" controls>
                                    <source src="{{ $selectedFileUrl }}">
                                    {{ __('Your browser does not support the audio tag') }}
                                </audio>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center justify-center w-full h-full gap-2" x-show="!$wire.selectedFileId">
                        <x-form.button @click="$wire.dispatchTo('modals.search-node', 'open-modal')">
                            <x-icons.magnifyingGlass class="w-4 h-4" />
                            <span class="hidden text-sm font-medium md:block">{{ __('Open file') }}</span>
                        </x-form.button>
                        <x-form.button primary @click="$wire.dispatchTo('modals.add-node', 'open-modal')">
                            <x-icons.plus class="w-4 h-4" />
                            <span class="hidden text-sm font-medium md:block">{{ __('New note') }}</span>
                        </x-form.button>
                    </div>
                </div>
            </div>

            <div class="absolute top-0 right-0 z-30 flex flex-col h-full overflow-hidden overflow-y-auto transition-all w-60 bg-light-base-50 dark:bg-base-900"
                :class="{ 'translate-x-0': isRightPanelOpen, '-translate-x-full hidden': !isRightPanelOpen }"
            >
                <div class="flex flex-col gap-4 p-4">
                    <div class="flex flex-col w-full gap-2">
                        <h3>Links</h3>
                        <div class="flex flex-col gap-2 text-sm">
                            @if ($this->selectedFile && $this->selectedFile->links->count())
                                @foreach ($this->selectedFile->links as $link)
                                    <a class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                        href=""
                                        wire:key="file-link-{{ $link->id }}"
                                        @click.prevent="openFile({{ $link->id }})"
                                    >{{ $link->name }}</a>
                                @endforeach
                            @else
                                <p>{{ __('No links found') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col w-full gap-2">
                        <h3>Backlinks</h3>
                        <div class="flex flex-col gap-2 text-sm">
                            @if ($this->selectedFile && $this->selectedFile->backlinks->count())
                                @foreach ($this->selectedFile->backlinks as $link)
                                    <a class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                        href=""
                                        wire:key="file-backlink-{{ $link->id }}"
                                        @click.prevent="openFile({{ $link->id }})"
                                    >{{ $link->name }}</a>
                                @endforeach
                            @else
                                <p>{{ __('No backlinks found') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col w-full gap-2">
                        <h3>Tags</h3>
                        <div class="flex flex-col gap-2 text-sm">
                            @if ($this->selectedFile && $this->selectedFile->tags->count())
                                @foreach ($this->selectedFile->tags as $tag)
                                    <a class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                        href=""
                                        wire:key="file-tag-{{ $tag->id }}"
                                        @click.prevent="$wire.dispatchTo('modals.search-node', 'open-modal', { search: 'tag:{{ $tag->name }}' })"
                                    >{{ $tag->name }}</a>
                                @endforeach
                            @else
                                <p>{{ __('No tags found') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-layouts.appMain>

    <livewire:modals.add-node :vault="$this->vault" />
    <livewire:modals.import-file :vault="$this->vault" />
    <livewire:modals.edit-node :vault="$this->vault" />
    <livewire:modals.search-node :vault="$this->vault" />
    <livewire:modals.markdown-editor-search :vault="$this->vault" />
    <livewire:modals.markdown-editor-template :vault="$this->vault" />

    @if ($this->vault->created_by === auth()->user()->id)
        <livewire:modals.collaboration :vault="$this->vault" />
    @endif
</div>

@script
    <script>
        Alpine.data('vault', () => ({
            isLeftPanelOpen: false,
            isRightPanelOpen: false,
            isEditMode: Alpine.$persist(true),
            selectedFileId: $wire.entangle('selectedFileId'),
            selectedFileExtension: $wire.entangle('selectedFileExtension'),
            selectedFileRefreshes: $wire.entangle('selectedFileRefreshes'),
            html: '',
            users: [],

            init() {
                if (this.selectedFileId !== null && $wire.toastErrorMessage.length === 0) {
                    this.startVaultNodeEventListeners();
                }

                if ($wire.toastErrorMessage.length > 0) {
                    this.$nextTick(() => {
                        this.$dispatch('toast', { message: $wire.toastErrorMessage, type: 'error' });
                    });
                }

                this.isLeftPanelOpen = !this.isSmallDevice();

                if (!this.isEditMode) {
                    this.$nextTick(() => { this.markdownToHtml() });
                }

                this.$watch('isEditMode', value => {
                    if (value) {
                        return;
                    }

                    this.markdownToHtml();
                });

                this.$watch('selectedFileId', value => {
                    if (value === null) {
                        this.html = '';
                        return;
                    }

                    this.markdownToHtml();
                    this.startVaultNodeEventListeners();
                });

                this.$watch('selectedFileRefreshes', value => {
                    if (value === 0) {
                        return;
                    }

                    this.markdownToHtml();
                });

                Echo.private('User.{{ auth()->user()->id }}')
                    .listen('CollaborationDeletedEvent', (e) => {
                        if (e.vault_id !== {{ $this->vault->id }}) {
                            return;
                        }

                        $wire.checkPermission();
                    });
            },

            isSmallDevice() {
                return window.innerWidth < 768;
            },

            toggleLeftPanel() {
                this.isLeftPanelOpen = !this.isLeftPanelOpen;
            },

            toggleRightPanel() {
                this.isRightPanelOpen = !this.isRightPanelOpen;
            },

            closePanels() {
                this.isLeftPanelOpen = false;
                this.isRightPanelOpen = false;
            },

            toggleEditMode() {
                this.isEditMode = !this.isEditMode;
            },

            openFile(nodeId) {
                if (nodeId !== this.selectedFileId) {
                    this.stopVaultNodeEventListeners();
                }

                $wire.openFileId(nodeId);

                if (this.isSmallDevice()) {
                    this.closePanels();
                }

                this.resetScrollPosition();
            },

            resetScrollPosition() {
                if (!Number.isInteger(this.selectedFileId)) {
                    return;
                }

                const scrollElementId = this.isEditMode ? 'noteEdit' : 'nodeContainer';
                if (document.getElementById(scrollElementId) == null) {
                    return;
                }

                document.getElementById(scrollElementId).scrollTop = 0;
            },

            markdownToHtml() {
                const el = document.getElementById('noteEdit');

                if (!el) {
                    this.html = '';

                    return;
                }

                const node = this.selectedFileId;
                const renderer = {
                    image(token) {
                        let html = '';

                        if (token.href.startsWith('http://') || token.href.startsWith('https://')) {
                            // external images
                            html = '<img src="' + token.href + '" alt="' + token.text + '" />';
                        } else {
                            // internal images
                            html = '<img src="/files/{{ $this->vault->id }}?path=' + token.href + '&node=' +
                                node + '" alt="' + token.text + '" />';
                        }

                        return '<span class="flex items-center justify-center">' + html + '</span>';
                    },
                    link(token) {
                        // external links
                        if (token.href.startsWith('http://') || token.href.startsWith('https://')) {
                            return '<a href="' + token.href + '" title="' + (token.title ?? '') +
                                '" target="_blank">' + token.text + '</a>';
                        }

                        // internal links
                        return '<a href="" wire:click.prevent="openFilePath(\'' + token.href +
                            '\')" title="' + (token.title ?? '') + '">' + token.text + '</a>';
                    },
                };
                marked.use({
                    renderer,
                });
                this.html = DOMPurify
                    // sanitize markdown
                    .sanitize(marked.parse(el.value), {
                        ADD_ATTR: ['wire:click.prevent'],
                    })
                    // improve check lists design
                    .replaceAll('<li><input', '<li class="task-list-item"><input class="task-list-item-checkbox"');
            },

            startVaultNodeEventListeners() {
                if (this.selectedFileId === null) {
                    return;
                }

                Echo.join('VaultNode.' + this.selectedFileId)
                    .here((users) => {
                        this.users = users;
                    })
                    .joining((user) => {
                        this.users.push(user);
                    })
                    .leaving((user) => {
                        this.users = this.users.filter(u => u.id !== user.id);
                    })
                    .listen('VaultNodeUpdatedEvent', (e) => {
                        $wire.refreshFile(this.selectedFileId);
                    })
                    .listen('VaultNodeDeletedEvent', (e) => {
                        $wire.dispatch('file-close');
                    });
            },

            stopVaultNodeEventListeners() {
                if (this.selectedFileId === null) {
                    return;
                }

                Echo.leave('VaultNode.' + this.selectedFileId);
            },
        }));
    </script>
@endscript
