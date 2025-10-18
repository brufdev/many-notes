<div
    class="flex flex-col h-dvh print:h-auto"
    x-data="vault"
    @mde-link.window="editor.toggleLink($event.detail.path)"
    @mde-image.window="editor.setImage($event.detail.path)"
    @open-file.window="openFile($event.detail.id)"
    @open-new-file.window="openFile($event.detail.id, true)"
    @file-opened.window="$nextTick(() => { fileOpened($event.detail.autofocus) })"
    @file-refreshed.window="$nextTick(() => { fileRefreshed() })"
>
    <x-layouts.appHeader>
        <div class="flex items-center gap-3">
            <button
                class="hover:text-light-base-950 dark:hover:text-base-50"
                type="button"
                @click="toggleLeftPanel"
            >
                <x-icons.bars3BottomLeft class="w-5 h-5" />
            </button>
            <button
                class="hover:text-light-base-950 dark:hover:text-base-50"
                type="button"
                @click="$wire.dispatchTo('modals.search-node', 'open-modal')"
            >
                <x-icons.magnifyingGlass class="w-5 h-5" />
            </button>
        </div>

        <div class="flex items-center gap-3">
            <livewire:layout.notification-menu />
            <livewire:layout.user-menu />
            <button
                class="hover:text-light-base-950 dark:hover:text-base-50"
                type="button"
                @click="toggleRightPanel"
            >
                <x-icons.bars3BottomRight class="w-5 h-5" />
            </button>
        </div>
    </x-layouts.appHeader>

    <x-layouts.appMain>
        <div
            class="fixed inset-0 z-40 opacity-50 bg-light-base-50 dark:bg-base-900"
            wire:loading
            wire:target.except="nodeForm.name, nodeForm.content"
        >
            <div class="flex items-center justify-center h-full">
                <x-icons.spinner class="w-5 h-5 animate-spin" />
            </div>
        </div>

        <div
            class="fixed inset-0 z-20 opacity-50 bg-light-base-50 dark:bg-base-900"
            x-show="(isLeftPanelOpen || isRightPanelOpen) && isNotExtraLargeDevice()"
            @click="closePanels"
            x-transition:enter="ease-out duration-300"
            x-transition:leave="ease-in duration-200"
        ></div>

        <div
            class="absolute xl:static flex flex-col top-0 left-0 bottom-0 z-30 w-[80%] max-w-[300px] xl:w-[20%] xl:max-w-[300px] bg-light-base-200 dark:bg-base-800 transition-all overflow-x-auto overflow-y-auto print:hidden"
            :class="{ 'translate-x-0': isLeftPanelOpen, '-translate-x-full hidden': !isLeftPanelOpen }"
            x-cloak
        >
            <livewire:vault.tree-view lazy="on-load" :vault="$this->vault" />
        </div>

        <div class="flex-1 h-full max-w-full transition-all" x-cloak>
            <div
                class="flex flex-col h-full w-full transition-maxwidth duration-300 ease-in-out mx-auto"
                :class="{ 'max-w-full': isContentWidthFull, 'max-w-[48rem]': !isContentWidthFull }"
            >
                <div class="flex h-full w-full" x-show="$wire.selectedFileId">
                    <x-vault.fileDetails wire:key="file-details-{{ $nodeForm->extension }}">
                        @if (in_array($nodeForm->extension, App\Services\VaultFiles\Types\Note::extensions()))
                            <x-slot:header>
                                <x-tiptapEditor.toolbar />
                            </x-slot:header>
                            <div
                                class="h-full w-full px-4"
                                :class="isEditingMarkdown ? 'hidden' : ''"
                                spellcheck="false"
                                x-ref="noteEditor"
                                wire:ignore
                            ></div>
                            <div
                                class="h-full w-full px-4 whitespace-pre-wrap focus:outline-none"
                                :class="isEditingMarkdown ? '' : 'hidden'"
                                :contenteditable="isEditMode ? 'plaintext-only' : 'false'"
                                spellcheck="false"
                                x-ref="noteMarkdown"
                                wire:ignore
                                @input="editor.setContent(event.target.textContent)"
                            ></div>
                        @elseif (in_array($nodeForm->extension, App\Services\VaultFiles\Types\Image::extensions()))
                            <div class="w-full px-4">
                                <img src="{{ $selectedFileUrl }}" alt="" />
                            </div>
                        @elseif (in_array($nodeForm->extension, App\Services\VaultFiles\Types\Pdf::extensions()))
                            <div class="w-full px-4">
                                <object
                                    class="w-full h-full"
                                    type="application/pdf"
                                    data="{{ $selectedFileUrl }}"
                                ></object>
                            </div>
                        @elseif (in_array($nodeForm->extension, App\Services\VaultFiles\Types\Video::extensions()))
                            <div class="w-full px-4">
                                <video class="w-full" controls>
                                    <source src="{{ $selectedFileUrl }}" />
                                    {{ __('Your browser does not support the video tag') }}
                                </video>
                            </div>
                        @elseif (in_array($nodeForm->extension, App\Services\VaultFiles\Types\Audio::extensions()))
                            <div class="w-full px-4">
                                <audio class="w-full" controls>
                                    <source src="{{ $selectedFileUrl }}">
                                    {{ __('Your browser does not support the audio tag') }}
                                </audio>
                            </div>
                        @endif
                    </x-vault.fileDetails>
                </div>
                <div
                    class="flex flex-col w-full h-full"
                    x-show="!$wire.selectedFileId"
                >
                    <div class="flex gap-2 items-center justify-between p-4">
                        <div class="text-lg font-semibold">
                            {{ __('Recent files') }}
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                title="{{ __('New note') }}"
                                @click="$wire.dispatchTo('modals.add-node', 'open-modal')"
                            >
                                <x-icons.plus class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-col flex-grow w-full px-4 -mt-2 overflow-y-auto">
                        <template x-for="file in recentFiles" :key="file.id">
                            <button
                                class="flex flex-col gap-2 w-full text-start pt-2 pb-4 border-b last:border-b-0 border-light-base-300 dark:border-base-500 hover:text-primary-600 dark:hover:text-primary-300"
                                @click="openFile(file.id)"
                            >
                                <span class="flex items-center justify-between w-full">
                                    <span
                                        class="flex-grow overflow-hidden whitespace-nowrap text-ellipsis"
                                        :title="file.name"
                                        x-text="file.name"
                                    ></span>
                                    <span
                                        class="pl-2 text-xs text-light-base-700 dark:text-base-400"
                                        x-text="file.time_elapsed"
                                    ></span>
                                </span>
                                <span
                                    class="overflow-hidden text-xs whitespace-nowrap text-ellipsis text-light-base-700 dark:text-base-200"
                                    :title="file.full_path"
                                    x-text="file.full_path"
                                ></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="absolute xl:static flex flex-col top-0 right-0 bottom-0 z-30 w-[80%] max-w-[300px] xl:w-[20%] xl:max-w-[300px] py-4 bg-light-base-200 dark:bg-base-800 transition-all overflow-x-auto overflow-y-auto print:hidden"
            :class="{ 'translate-x-0': isRightPanelOpen, '-translate-x-full hidden': !isRightPanelOpen }"
            x-cloak
        >
            <div class="flex flex-col gap-4 px-4 overflow-y-auto">
                @if ($this->selectedFile)
                    <div class="flex flex-col w-full gap-4">
                        <div class="overflow-hidden whitespace-nowrap text-ellipsis text-lg font-semibold">
                            {{ __('Links') }}
                        </div>
                        <div class="flex flex-col gap-2 text-sm">
                            @forelse ($this->links as $link)
                                <a
                                    class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                    href=""
                                    @click.prevent="openFile({{ $link->id }})"
                                    wire:key="file-link-{{ $link->id }}"
                                >
                                    <span class="flex items-center justify-between w-full">
                                        <span class="flex-grow overflow-hidden whitespace-nowrap text-ellipsis" title="{{ $link->name }}">{{ $link->name }}</span>
                                        <span class="pl-2 text-xs text-light-base-700 dark:text-base-400">{{ $link->total }}</span>
                                    </span>
                                </a>
                            @empty
                                <p>{{ __('No links found') }}</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="flex flex-col w-full gap-4">
                        <div class="overflow-hidden whitespace-nowrap text-ellipsis text-lg font-semibold">
                            {{ __('Backlinks') }}
                        </div>
                        <div class="flex flex-col gap-2 text-sm">
                            @forelse ($this->backlinks as $link)
                                <a
                                    class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                    href=""
                                    wire:key="file-backlink-{{ $link->id }}"
                                    @click.prevent="openFile({{ $link->id }})"
                                >
                                    <span class="flex items-center justify-between w-full">
                                        <span class="flex-grow overflow-hidden whitespace-nowrap text-ellipsis" title="{{ $link->name }}">{{ $link->name }}</span>
                                        <span class="pl-2 text-xs text-light-base-700 dark:text-base-400">{{ $link->total }}</span>
                                    </span>
                                </a>
                            @empty
                                <p>{{ __('No backlinks found') }}</p>
                            @endforelse
                        </div>
                    </div>
                @endif
                <div class="flex flex-col w-full gap-4">
                    <div class="overflow-hidden whitespace-nowrap text-ellipsis text-lg font-semibold">
                        {{ __('Tags') }}
                    </div>
                    <div class="flex flex-col gap-2 text-sm">
                        @forelse ($this->tags as $tag)
                            <a
                                class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                href=""
                                wire:key="file-tag-{{ $tag->id }}"
                                @click.prevent="$wire.dispatchTo('modals.search-node', 'open-modal', { search: 'tag:{{ $tag->name }}' })"
                            >
                                <span class="flex items-center justify-between w-full">
                                    <span class="flex-grow overflow-hidden whitespace-nowrap text-ellipsis" title="{{ $tag->name }}">#{{ $tag->name }}</span>
                                    <span class="pl-2 text-xs text-light-base-700 dark:text-base-400">{{ $tag->total }}</span>
                                </span>
                            </a>
                        @empty
                            <p>{{ __('No tags found') }}</p>
                        @endforelse
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
            defaultLeftPanelOpen: Alpine.$persist(true),
            defaultRightPanelOpen: Alpine.$persist(true),
            isLeftPanelOpen: false,
            isRightPanelOpen: false,
            isContentWidthFull: Alpine.$persist(false),
            showToggleContentWidthButton: false,
            recentFiles: $wire.entangle('recentFiles'),
            isEditingMarkdown: Alpine.$persist(false),
            isEditMode: Alpine.$persist(true),
            editor: null,
            users: [],
            updateContent: Alpine.debounce((markdown) => {
                if ($wire.nodeForm.content === markdown) {
                    return;
                }

                $wire.$set('nodeForm.content', markdown);
            }, 500),

            init() {
                if ($wire.selectedFileId !== null && $wire.toastErrorMessage.length === 0) {
                    if ($wire.nodeForm.extension == 'md') {
                        this.initializeEditor();
                    }

                    this.startVaultNodeEventListeners();
                }

                if ($wire.toastErrorMessage.length > 0) {
                    this.$nextTick(() => {
                        this.$dispatch('toast', { message: $wire.toastErrorMessage, type: 'error' });
                    });
                }

                this.handleResponsiveLayout();
                window.onresize = () => this.handleResponsiveLayout();

                Echo.private('User.{{ auth()->user()->id }}')
                    .listen('CollaborationDeletedEvent', (e) => {
                        if (e.vault_id !== $wire.vaultId) {
                            return;
                        }

                        $wire.checkPermission();
                    });
            },

            initializeEditor(autofocus = false) {
                if (this.editor) {
                    this.editor.destroyEditor();
                }

                this.editor = setupEditor({
                    placeholder: '{{ __('Start writing your note...') }}',
                    element: this.$refs.noteEditor,
                    autofocus: autofocus,
                    markdownElement: this.$refs.noteMarkdown,
                    vaultId: $wire.nodeForm.vaultId,
                    content: $wire.nodeForm.content,
                    isEditingMarkdown: this.isEditingMarkdown,
                    editable: this.isEditMode,
                    onUpdate: (markdown) => this.updateContent(markdown),
                });
            },

            isMediumDevice() {
                return window.innerWidth > 768;
            },

            isNotExtraLargeDevice() {
                return window.innerWidth < 1280;
            },

            handleResponsiveLayout() {
                if (this.isNotExtraLargeDevice()) {
                    this.closePanels();
                } else {
                    this.isLeftPanelOpen = this.defaultLeftPanelOpen;
                    this.isRightPanelOpen = this.defaultRightPanelOpen;
                }

                this.showToggleContentWidthButton = this.isMediumDevice();
            },

            toggleLeftPanel() {
                if (!this.isNotExtraLargeDevice()) {
                    this.defaultLeftPanelOpen = !this.defaultLeftPanelOpen;
                }

                this.isLeftPanelOpen = !this.isLeftPanelOpen;
            },

            toggleRightPanel() {
                if (!this.isNotExtraLargeDevice()) {
                    this.defaultRightPanelOpen = !this.defaultRightPanelOpen;
                }

                this.isRightPanelOpen = !this.isRightPanelOpen;
            },

            closePanels() {
                this.isLeftPanelOpen = false;
                this.isRightPanelOpen = false;
            },

            toggleContentWidth() {
                this.isContentWidthFull = !this.isContentWidthFull;
            },

            toggleMarkdown() {
                this.isEditingMarkdown = !this.isEditingMarkdown;
                this.editor.toggleMarkdown();
            },

            toggleEditMode() {
                this.isEditMode = !this.isEditMode;
                this.editor.setEditable(this.isEditMode);
            },

            openFile(fileId, autofocus = false) {
                const previousSelectedFileId = $wire.selectedFileId;

                if (fileId !== previousSelectedFileId) {
                    this.stopVaultNodeEventListeners();

                    if (this.editor) {
                        this.editor.destroyEditor();
                    }
                }

                $wire.openFileId(fileId, autofocus);

                if (this.isNotExtraLargeDevice()) {
                    this.closePanels();
                }
            },

            fileOpened(autofocus = false) {
                this.startVaultNodeEventListeners();

                if ($wire.nodeForm.extension !== 'md') {
                    return;
                }

                this.initializeEditor(autofocus);

                if (autofocus && this.isEditingMarkdown) {
                    this.$refs.noteMarkdown.focus();
                }
            },

            fileRefreshed() {
                if ($wire.nodeForm.extension == 'md') {
                    this.initializeEditor();
                }
            },

            closeFile() {
                this.stopVaultNodeEventListeners();
                $wire.closeFile();
            },

            startVaultNodeEventListeners() {
                if ($wire.selectedFileId === null) {
                    return;
                }

                Echo.join('VaultNode.' + $wire.selectedFileId)
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
                        $wire.refreshFile($wire.selectedFileId);
                    })
                    .listen('VaultNodeDeletedEvent', (e) => {
                        this.closeFile();
                    });
            },

            stopVaultNodeEventListeners() {
                if ($wire.selectedFileId === null) {
                    return;
                }

                Echo.leave('VaultNode.' + $wire.selectedFileId);
            },
        }));
    </script>
@endscript
