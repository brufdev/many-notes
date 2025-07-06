<div {{ $attributes }} class="flex justify-between gap-1">
    <div class="relative">
        <ul class="flex gap-1">
            <li>
                <x-tiptapEditor.button @click="editor.undo()">
                    <svg class="w-4.5 h-4.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9h13a5 5 0 0 1 0 10H7M3 9l4-4M3 9l4 4"/>
                    </svg>
                    <span class="sr-only">{{ __('Undo') }}</span>
                </x-tiptapEditor.button>
            </li>
            <li>
                <x-tiptapEditor.button @click="editor.redo()">
                    <svg class="w-4.5 h-4.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 9H8a5 5 0 0 0 0 10h9m4-10-4-4m4 4-4 4"/>
                    </svg>
                    <span class="sr-only">{{ __('Redo') }}</span>
                </x-tiptapEditor.button>
            </li>
            <x-tiptapEditor.itemDropdown>
                <x-tiptapEditor.button title="{{ __('Style') }}">
                    <x-icons.pilcrow class="w-4.5 h-4.5" />
                    <span class="sr-only">{{ __('Style') }}</span>
                </x-tiptapEditor.button>
                <x-tiptapEditor.items x-show="isToolbarOpen" x-anchor.bottom="$refs.button">
                    <li class="flex">
                        <x-tiptapEditor.subButton @click="editor.setParagraph()" title="{{ __('Paragraph') }}">
                            <x-icons.pilcrow class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Paragraph') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.toggleBlockquote()" title="{{ __('Quote') }}">
                            <x-icons.quote class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Quote') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.toggleCodeBlock()" title="{{ __('Code block') }}">
                            <x-icons.code class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Code block') }}</span>
                        </x-tiptapEditor.subButton>
                    </li>
                    <x-tiptapEditor.itemDivider />
                    <li class="flex">
                        <x-tiptapEditor.subButton @click="editor.toggleHeading({ level: 1 })" title="{{ __('Heading 1') }}">
                            <x-icons.heading1 class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Heading 1') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.toggleHeading({ level: 2 })" title="{{ __('Heading 2') }}">
                            <x-icons.heading2 class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Heading 2') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.toggleHeading({ level: 3 })" title="{{ __('Heading 3') }}">
                            <x-icons.heading3 class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Heading 3') }}</span>
                        </x-tiptapEditor.subButton>
                    </li>
                    <li class="flex">
                        <x-tiptapEditor.subButton @click="editor.toggleHeading({ level: 4 })" title="{{ __('Heading 4') }}">
                            <x-icons.heading4 class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Heading 4') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.toggleHeading({ level: 5 })" title="{{ __('Heading 5') }}">
                            <x-icons.heading5 class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Heading 5') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.toggleHeading({ level: 6 })" title="{{ __('Heading 6') }}">
                            <x-icons.heading6 class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Heading 6') }}</span>
                        </x-tiptapEditor.subButton>
                    </li>
                    <x-tiptapEditor.itemDivider />
                    <li class="flex">
                        <x-tiptapEditor.subButton @click="editor.toggleBulletList()" title="{{ __('List') }}">
                            <x-icons.list class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('List') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.toggleOrderedList()" title="{{ __('Ordered list') }}">
                            <x-icons.listOrdered class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Ordered list') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.toggleTaskList()" title="{{ __('Task list') }}">
                            <x-icons.listTodo class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Task list') }}</span>
                        </x-tiptapEditor.subButton>
                    </li>
                </x-tiptapEditor.items>
            </x-tiptapEditor.itemDropdown>
            <x-tiptapEditor.itemDropdown>
                <x-tiptapEditor.button title="{{ __('Format') }}">
                    <x-icons.type class="w-4.5 h-4.5" />
                    <span class="sr-only">{{ __('Format') }}</span>
                </x-tiptapEditor.button>
                <x-tiptapEditor.items x-show="isToolbarOpen" x-anchor.bottom="$refs.button">
                    <li class="flex">
                        <x-tiptapEditor.subButton @click="editor.toggleBold()" title="{{ __('Bold') }}">
                            <x-icons.bold class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Bold') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.toggleItalic()" title="{{ __('Italic') }}">
                            <x-icons.italic class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Italic') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.toggleStrike()" title="{{ __('Strike') }}">
                            <x-icons.strike class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Strike') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.toggleCode()" title="{{ __('Inline code') }}">
                            <x-icons.codeInline class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Inline code') }}</span>
                        </x-tiptapEditor.subButton>
                    </li>
                </x-tiptapEditor.items>
            </x-tiptapEditor.itemDropdown>
            <x-tiptapEditor.itemDropdown>
                <x-tiptapEditor.button title="{{ __('Insert') }}">
                    <x-icons.fileInput class="w-4.5 h-4.5" />
                    <span class="sr-only">{{ __('Insert') }}</span>
                </x-tiptapEditor.button>
                <x-tiptapEditor.items x-show="isToolbarOpen" x-anchor.bottom="$refs.button">
                    <li class="flex">
                        <x-tiptapEditor.subButton @click="$dispatch('toggle-mde-search-link-modal', { url: editor.getEditor().getAttributes('link').href ?? '' })" title="{{ __('Link') }}">
                            <x-icons.link class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Link') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="$dispatch('toggle-mde-search-image-modal', { url: editor.getEditor().getAttributes('image').src ?? '' })" title="{{ __('Image') }}">
                            <x-icons.image class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Image') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.setHorizontalRule()" title="{{ __('Horizontal rule') }}">
                            <x-icons.horizontalRule class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Horizontal rule') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="$wire.dispatchTo('modals.markdown-editor-template', 'open-modal', { selectedFile: $wire.selectedFileId })" title="{{ __('Template') }}">
                            <x-icons.template class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Template') }}</span>
                        </x-tiptapEditor.subButton>
                    </li>
                </x-tiptapEditor.items>
            </x-tiptapEditor.itemDropdown>
            <x-tiptapEditor.itemDropdown>
                <x-tiptapEditor.button title="{{ __('Table') }}">
                    <x-icons.tableAdd class="w-4.5 h-4.5" />
                    <span class="sr-only">{{ __('Table') }}</span>
                </x-tiptapEditor.button>
                <x-tiptapEditor.items x-show="isToolbarOpen" x-anchor.bottom="$refs.button">
                    <li class="flex">
                        <x-tiptapEditor.subButton @click="editor.insertTable()" title="{{ __('Insert table') }}">
                            <x-icons.tableAdd class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Insert table') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.deleteTable()" title="{{ __('Delete table') }}">
                            <x-icons.tableDelete class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Delete table') }}</span>
                        </x-tiptapEditor.subButton>
                    </li>
                    <x-tiptapEditor.itemDivider />
                    <li class="flex">
                        <x-tiptapEditor.subButton @click="editor.addColumnBefore()" title="{{ __('Add column before') }}">
                            <x-icons.tableAddColumn class="w-4.5 h-4.5 rotate-180" />
                            <span class="sr-only">{{ __('Add column before') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.addColumnAfter()" title="{{ __('Add column after') }}">
                            <x-icons.tableAddColumn class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Add column after') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.deleteColumn()" title="{{ __('Delete column') }}">
                            <x-icons.tableDeleteColumn class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Delete column') }}</span>
                        </x-tiptapEditor.subButton>
                    </li>
                    <li class="flex">
                        <x-tiptapEditor.subButton @click="editor.addRowBefore()" title="{{ __('Add row before') }}">
                            <x-icons.tableAddRow class="w-4.5 h-4.5 rotate-180" />
                            <span class="sr-only">{{ __('Add row before') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.addRowAfter()" title="{{ __('Add row after') }}">
                            <x-icons.tableAddRow class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Add row after') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.deleteRow()" title="{{ __('Delete row') }}">
                            <x-icons.tableDeleteRow class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Delete row') }}</span>
                        </x-tiptapEditor.subButton>
                    </li>
                    <x-tiptapEditor.itemDivider />
                    <li class="flex">
                        <x-tiptapEditor.subButton @click="editor.setTableColumnAlignmentLeft()" title="{{ __('Align left') }}">
                            <x-icons.alignLeft class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Align left') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.setTableColumnAlignmentCenter()" title="{{ __('Align center') }}">
                            <x-icons.alignCenter class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Align center') }}</span>
                        </x-tiptapEditor.subButton>
                        <x-tiptapEditor.subButton @click="editor.setTableColumnAlignmentRight()" title="{{ __('Align right') }}">
                            <x-icons.alignRight class="w-4.5 h-4.5" />
                            <span class="sr-only">{{ __('Align right') }}</span>
                        </x-tiptapEditor.subButton>
                    </li>
                </x-tiptapEditor.items>
            </x-tiptapEditor.itemDropdown>
        </ul>
        <div x-show="!isEditMode" class="absolute inset-0 opacity-50 bg-light-base-200 dark:bg-base-950"></div>
    </div>
    <ul class="flex gap-1">
        <li>
            <x-tiptapEditor.button
                x-bind:class="!isEditMode ? 'bg-light-base-400 dark:bg-base-700' : ''"
                title="{{ __('Read mode') }}"
                @click="toggleEditMode"
            >
                <x-icons.eye class="w-4.5 h-4.5" />
                <span class="sr-only">{{ __('Read mode') }}</span>
            </x-tiptapEditor.button>
        </li>
    </ul>
</div>
