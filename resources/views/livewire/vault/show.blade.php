<div class="flex flex-col h-dvh">
    <x-layouts.appHeader>
        <div class="flex items-center gap-4">
            <button type="button" class="hover:text-light-base-950 dark:hover:text-base-50"
                @click="$dispatch('left-panel-toggle')"
            >
                <x-icons.bars3BottomLeft class="w-5 h-5" />
            </button>
        </div>

        <div class="flex items-center gap-4">
            <button type="button" class="hover:text-light-base-950 dark:hover:text-base-50"
                @click="$wire.dispatchTo('modals.search-node', 'open-modal')"
            >
                <x-icons.magnifyingGlass class="w-5 h-5" />
            </button>
            <div class="flex items-center gap-4">
                <livewire:layout.user-menu />
            </div>
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
                <div class="sticky top-0 z-[5] flex justify-between p-4 bg-light-base-50 dark:bg-base-900">
                    <h3>{{ $vault->name }}</h3>

                    <div class="flex items-center">
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
                </div>

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
                                        <x-menu>
                                            <x-menu.button>
                                                <x-icons.bars3 class="w-5 h-5" />
                                            </x-menu.button>
                                            <x-menu.items>
                                                <x-modal x-show="selectedFileExtension == 'md' && isEditMode">
                                                    <x-modal.open>
                                                        <x-menu.close>
                                                            <x-menu.item>
                                                                <x-icons.documentDuplicate class="w-4 h-4" />
                                                                {{ __('Insert template') }}
                                                            </x-menu.item>
                                                        </x-menu.close>
                                                    </x-modal.open>
                                                    <x-modal.panel title="{{ __('Choose a template') }}">
                                                        @if ($templates && count($templates))
                                                            <ul class="flex flex-col gap-2" wire:loading.class="opacity-50">
                                                                @foreach ($templates as $template)
                                                                    <li wire:key="{{ $template->id }}">
                                                                        <button type="button"
                                                                            class="flex w-full gap-2 py-1 hover:text-light-base-950 dark:hover:text-base-50"
                                                                            wire:click="insertTemplate({{ $template->id }}); modalOpen = false"
                                                                        >
                                                                            <span class="overflow-hidden whitespace-nowrap text-ellipsis"
                                                                                title="{{ $template->name }}"
                                                                            >
                                                                                {{ $template->name }}
                                                                            </span>
                                                                        </button>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            <p>{{ __('No templates found') }}</p>
                                                        @endif
                                                    </x-modal.panel>
                                                </x-modal>

                                                <x-menu.close>
                                                    <x-menu.item wire:click="closeFile">
                                                        <x-icons.xMark class="w-4 h-4" />
                                                        {{ __('Close file') }}
                                                    </x-menu.item>
                                                </x-menu.close>
                                            </x-menu.items>
                                        </x-menu>
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
            renderListitem: null,

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
                let markedRender = new marked.Renderer;
                markedRender.parser = new marked.Parser;
                this.renderListitem = markedRender.listitem.bind(markedRender);
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
                let el = document.getElementById('noteEdit');
                let markdown = '';
                let renderListitem = this.renderListitem;
                let node = this.selectedFile;

                if (!el) {
                    this.html = markdown;

                    return;
                }

                renderer = {
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
                    listitem(token) {
                        let html = renderListitem(token);

                        if (token.task) {
                            html = html.replace('<li>', '<li class="task-list-item">')
                                .replace('<input ', '<input class="task-list-item-checkbox" ');
                        }

                        return html;
                    },
                };
                marked.use({
                    renderer
                });

                this.html = DOMPurify.sanitize(marked.parse(el.value), {
                    ADD_ATTR: ['wire:click.prevent'],
                });
            },
        }))
    </script>
@endscript
