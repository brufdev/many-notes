<div
    class="flex flex-col h-dvh"
    x-data="vault"
    @mde-link.window="editor.toggleLink($event.detail.path)"
    @mde-image.window="editor.setImage($event.detail.path)"
    @file-opened.window="$nextTick(() => { fileOpened() })"
    @file-refreshed.window="$nextTick(() => { fileRefreshed() })"
>
    <x-layouts.appHeader>
        <div class="flex items-center gap-4">
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

        <div class="flex items-center gap-4">
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
        <div class="relative flex w-full" x-cloak>
            <div
                class="fixed inset-0 z-40 opacity-50 bg-light-base-200 dark:bg-base-950"
                wire:loading wire:target.except="nodeForm.name, nodeForm.content"
            >
                <div class="flex items-center justify-center h-full">
                    <x-icons.spinner class="w-5 h-5 animate-spin" />
                </div>
            </div>
            <div
                class="fixed inset-0 z-20 opacity-50 bg-light-base-200 dark:bg-base-950"
                x-show="(isLeftPanelOpen || isRightPanelOpen) && isSmallDevice"
                @click="closePanels"
                x-transition:enter="ease-out duration-300"
                x-transition:leave="ease-in duration-200"
            ></div>
            <div
                class="absolute top-0 left-0 z-30 flex flex-col h-full overflow-hidden overflow-y-auto transition-all w-60 bg-light-base-50 dark:bg-base-900"
                :class="{ 'translate-x-0': isLeftPanelOpen, '-translate-x-full hidden': !isLeftPanelOpen }"
            >
                <livewire:vault.tree-view lazy="on-load" :vault="$this->vault" />
            </div>

            <div
                class="absolute top-0 bottom-0 right-0 flex flex-col w-full transition-all text-start bg-light-base-200 dark:bg-base-950"
                :class="{ 'md:pl-60': isLeftPanelOpen, 'md:pr-60': isRightPanelOpen }"
                id="nodeContainer"
            >
                <div class="flex flex-col h-full w-full max-w-[48rem] mx-auto px-4 overflow-y-auto">
                    <div class="flex flex-col w-full h-full" x-show="$wire.selectedFileId">
                        <x-vault.fileDetails>
                            @if (in_array($nodeForm->extension, App\Services\VaultFiles\Note::extensions()))
                                <x-slot:header>
                                    <x-tiptapEditor.toolbar />
                                </x-slot:header>
                                <textarea
                                    class="hidden"
                                    type="text"
                                    x-ref="noteContent"
                                    wire:model.live.debounce.1000ms="nodeForm.content"
                                ></textarea>
                                <div class="h-full" spellcheck="false" x-ref="noteEditor" wire:ignore></div>
                            @elseif (in_array($nodeForm->extension, App\Services\VaultFiles\Image::extensions()))
                                <img src="{{ $selectedFileUrl }}" alt="" />
                            @elseif (in_array($nodeForm->extension, App\Services\VaultFiles\Pdf::extensions()))
                                <object
                                    class="w-full h-full"
                                    type="application/pdf"
                                    data="{{ $selectedFileUrl }}"
                                ></object>
                            @elseif (in_array($nodeForm->extension, App\Services\VaultFiles\Video::extensions()))
                                <video class="w-full" controls>
                                    <source src="{{ $selectedFileUrl }}" />
                                    {{ __('Your browser does not support the video tag') }}
                                </video>
                            @elseif (in_array($nodeForm->extension, App\Services\VaultFiles\Audio::extensions()))
                                <audio class="w-full" controls>
                                    <source src="{{ $selectedFileUrl }}">
                                    {{ __('Your browser does not support the audio tag') }}
                                </audio>
                            @endif
                        </x-vault.fileDetails>
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

            <div
                class="absolute top-0 right-0 z-30 flex flex-col h-full overflow-hidden overflow-y-auto transition-all w-60 bg-light-base-50 dark:bg-base-900"
                :class="{ 'translate-x-0': isRightPanelOpen, '-translate-x-full hidden': !isRightPanelOpen }"
            >
                <div class="flex flex-col gap-4 p-4">
                    <div class="flex flex-col w-full gap-2">
                        <h3>{{ __('Links') }}</h3>
                        <div class="flex flex-col gap-2 text-sm">
                            @if ($this->selectedFile && $this->selectedFile->links->count())
                                @foreach ($this->selectedFile->links as $link)
                                    <a
                                        class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                        href=""
                                        @click.prevent="openFile({{ $link->id }})"
                                        wire:key="file-link-{{ $link->id }}"
                                    >
                                        {{ $link->name }}
                                    </a>
                                @endforeach
                            @else
                                <p>{{ __('No links found') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col w-full gap-2">
                        <h3>{{ __('Backlinks') }}</h3>
                        <div class="flex flex-col gap-2 text-sm">
                            @if ($this->selectedFile && $this->selectedFile->backlinks->count())
                                @foreach ($this->selectedFile->backlinks as $link)
                                    <a
                                        class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                        href=""
                                        wire:key="file-backlink-{{ $link->id }}"
                                        @click.prevent="openFile({{ $link->id }})"
                                    >
                                        {{ $link->name }}
                                    </a>
                                @endforeach
                            @else
                                <p>{{ __('No backlinks found') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col w-full gap-2">
                        <h3>{{ __('Tags') }}</h3>
                        <div class="flex flex-col gap-2 text-sm">
                            @if ($this->selectedFile && $this->selectedFile->tags->count())
                                @foreach ($this->selectedFile->tags as $tag)
                                    <a
                                        class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                        href=""
                                        wire:key="file-tag-{{ $tag->id }}"
                                        @click.prevent="$wire.dispatchTo('modals.search-node', 'open-modal', { search: 'tag:{{ $tag->name }}' })"
                                    >
                                        {{ $tag->name }}
                                    </a>
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
            editor: null,
            users: [],

            init() {
                if ($wire.selectedFileId !== null && $wire.toastErrorMessage.length === 0) {
                    this.initializeEditor();
                    this.startVaultNodeEventListeners();
                }

                if ($wire.toastErrorMessage.length > 0) {
                    this.$nextTick(() => {
                        this.$dispatch('toast', { message: $wire.toastErrorMessage, type: 'error' });
                    });
                }

                this.isLeftPanelOpen = !this.isSmallDevice();

                Echo.private('User.{{ auth()->user()->id }}')
                    .listen('CollaborationDeletedEvent', (e) => {
                        if (e.vault_id !== $wire.vaultId) {
                            return;
                        }

                        $wire.checkPermission();
                    });
            },

            initializeEditor() {
                if (this.editor) {
                    this.editor.destroyEditor();
                }

                this.editor = setupEditor({
                    placeholder: '{{ __('Start writing your note...') }}',
                    element: this.$refs.noteEditor,
                    vaultId: $wire.nodeForm.vaultId,
                    content: $wire.nodeForm.content,
                    editable: this.isEditMode,
                    onUpdate: (markdown) => {
                        if ($refs.noteContent.value === markdown) {
                            return;
                        }

                        $refs.noteContent.value = markdown;
                        $refs.noteContent.dispatchEvent(new Event('input'));
                    },
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
                this.editor.setEditable(this.isEditMode);
            },

            openFile(fileId) {
                const previousSelectedFileId = $wire.selectedFileId;

                if (fileId !== previousSelectedFileId) {
                    this.stopVaultNodeEventListeners();
                }

                $wire.openFileId(fileId);

                if (this.isSmallDevice()) {
                    this.closePanels();
                }
            },

            fileOpened() {
                this.startVaultNodeEventListeners();

                if ($wire.nodeForm.extension == 'md') {
                    this.initializeEditor();
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
                        $wire.dispatch('file-close');
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
