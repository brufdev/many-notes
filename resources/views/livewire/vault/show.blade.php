<div class="flex flex-col h-dvh">
    <x-layouts.appHeader>
        <div class="flex items-center gap-4">
            <button type="button" class="hover:text-light-base-950 dark:hover:text-base-50"
                @click="$dispatch('left-panel-toggle')"
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
                @click="$dispatch('right-panel-toggle')"
            >
                <x-icons.bars3BottomRight class="w-5 h-5" />
            </button>
        </div>
    </x-layouts.appHeader>

    <x-layouts.appMain>
        <div x-data="vault" x-cloak class="relative flex w-full"
            @left-panel-toggle.window="isLeftPanelOpen = !isLeftPanelOpen"
            @right-panel-toggle.window="isRightPanelOpen = !isRightPanelOpen"
            @file-render-markup.window="$nextTick(() => { markdownToHtml() })"
        >
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
                <livewire:vault.tree-view lazy="on-load" :$vault />
            </div>

            <div class="absolute top-0 bottom-0 right-0 flex flex-col w-full overflow-y-auto transition-all text-start bg-light-base-200 dark:bg-base-950"
                :class="{ 'md:pl-60': isLeftPanelOpen, 'md:pr-60': isRightPanelOpen }" id="nodeContainer"
            >
                <div class="flex flex-col h-full w-full max-w-[48rem] mx-auto p-4">
                    <div class="flex flex-col w-full h-full gap-4" x-show="$wire.selectedFile">
                        <div class="z-[5]">
                            <div class="flex justify-between">
                                <input type="text" wire:model.live.debounce.500ms="nodeForm.name"
                                    class="flex flex-grow p-0 px-1 text-lg bg-transparent border-0 focus:ring-0 focus:outline-0" />

                                <div class="flex items-center gap-2">
                                    <span class="flex items-center" wire:loading.flex wire:target="nodeForm.name, nodeForm.content">
                                        <x-icons.spinner class="w-4 h-4 animate-spin" />
                                    </span>
                                    <div class="flex gap-2">
                                        <button title="{{ __('Close file') }}" wire:click="closeFile">
                                            <x-icons.xMark class="w-5 h-5" />
                                        </button>
                                    </div>
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
                    <div class="flex items-center justify-center w-full h-full gap-2" x-show="!$wire.selectedFile">
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
                            @if ($nodeForm->node && $nodeForm->node->links->count())
                                @foreach ($nodeForm->node->links as $link)
                                    <a class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                        href="" @click.prevent="openFile({{ $link->id }})" wire:key="{{ $link->id }}"
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
                            @if ($nodeForm->node && $nodeForm->node->backlinks->count())
                                @foreach ($nodeForm->node->backlinks as $link)
                                    <a class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                        href="" @click.prevent="openFile({{ $link->id }})" wire:key="{{ $link->id }}"
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
                            @if ($nodeForm->node && $nodeForm->node->tags->count())
                                @foreach ($nodeForm->node->tags as $tag)
                                    <a href="" class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                        @click.prevent="$wire.dispatchTo('modals.search-node', 'open-modal', { search: 'tag:{{ $tag->name }}' })"
                                        wire:key="{{ $tag->id }}"
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

    <livewire:modals.add-node :$vault />
    <livewire:modals.import-file :$vault />
    <livewire:modals.edit-node :$vault />
    <livewire:modals.search-node :$vault />
    <livewire:modals.markdown-editor-search :$vault />
    <livewire:modals.markdown-editor-template :$vault />

    @if ($vault->created_by === auth()->user()->id)
        <livewire:modals.collaboration :$vault />
    @endif
</div>

@script
    <script>
        Alpine.data('vault', () => ({
            isLeftPanelOpen: false,
            isRightPanelOpen: false,
            isEditMode: $wire.entangle('isEditMode'),
            selectedFile: $wire.entangle('selectedFile'),
            selectedFileExtension: $wire.entangle('selectedFileExtension'),
            html: '',

            init() {
                this.$watch('isEditMode', value => {
                    if (value) {
                        return;
                    }
                    this.markdownToHtml();
                });

                this.$watch('selectedFile', value => {
                    if (value === null) {
                        this.html = '';
                        return;
                    }
                    this.markdownToHtml();
                });

                this.isLeftPanelOpen = !this.isSmallDevice();
            },

            isSmallDevice() {
                return window.innerWidth < 768;
            },

            closePanels() {
                this.isLeftPanelOpen = false;
                this.isRightPanelOpen = false;
            },

            toggleEditMode() {
                this.isEditMode = !this.isEditMode;
            },

            openFile(node) {
                $wire.openFile(node);

                if (this.isSmallDevice()) {
                    this.closePanels();
                }

                this.resetScrollPosition();
            },

            resetScrollPosition() {
                if (!Number.isInteger(this.selectedFile)) {
                    return;
                }

                let scrollElementId = this.isEditMode ? 'noteEdit' : 'nodeContainer';
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

                const node = this.selectedFile;
                const renderer = {
                    image(token) {
                        let html = '';

                        if (token.href.startsWith('http://') || token.href.startsWith('https://')) {
                            // external images
                            html = '<img src="' + token.href + '" alt="' + token.text + '" />';
                        } else {
                            // internal images
                            html = '<img src="/files/{{ $vault->id }}?path=' + token.href + '&node=' +
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
                    renderer
                });

                const sanitizedHtml = DOMPurify.sanitize(marked.parse(el.value), {
                    ADD_ATTR: ['wire:click.prevent'],
                });

                this.html = sanitizedHtml
                    // improve check lists design
                    .replaceAll('<li><input', '<li class="task-list-item"><input class="task-list-item-checkbox"');
            },
        }))
    </script>
@endscript
