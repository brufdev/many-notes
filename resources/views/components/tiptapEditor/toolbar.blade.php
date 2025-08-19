<div
    {{ $attributes }}
    class="flex justify-between gap-1"
    x-data="{ isToolbarLocked() { return !isEditMode || isEditingMarkdown } }"
>
    <div class="overflow-x-auto scrollbar-hidden">
        <ul class="flex gap-1">
            <li>
                <x-tiptapEditor.button
                    title="{{ __('Undo') }}"
                    icon="undo"
                    x-bind:disabled="isToolbarLocked()"
                    @click="editor.undo()"
                />
            </li>
            <li>
                <x-tiptapEditor.button
                    title="{{ __('Redo') }}"
                    icon="redo"
                    x-bind:disabled="isToolbarLocked()"
                    @click="editor.redo()"
                />
            </li>
            <x-tiptapEditor.itemDropdown>
                <x-tiptapEditor.button
                    title="{{ __('Headings') }}"
                    icon="heading"
                    x-bind:disabled="isToolbarLocked()"
                />
                <x-tiptapEditor.items
                    x-show="isToolbarOpen && !isToolbarLocked()"
                    x-anchor.bottom="$refs.button"
                >
                    <li class="flex">
                        <x-tiptapEditor.subButton
                            title="{{ __('Heading 1') }}"
                            icon="heading1"
                            @click="editor.toggleHeading({ level: 1 })"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Heading 2') }}"
                            icon="heading2"
                            @click="editor.toggleHeading({ level: 2 })"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Heading 3') }}"
                            icon="heading3"
                            @click="editor.toggleHeading({ level: 3 })"
                        />
                    </li>
                    <li class="flex">
                        <x-tiptapEditor.subButton
                            title="{{ __('Heading 4') }}"
                            icon="heading4"
                            @click="editor.toggleHeading({ level: 4 })"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Heading 5') }}"
                            icon="heading5"
                            @click="editor.toggleHeading({ level: 5 })"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Heading 6') }}"
                            icon="heading6"
                            @click="editor.toggleHeading({ level: 6 })"
                        />
                    </li>
                </x-tiptapEditor.items>
            </x-tiptapEditor.itemDropdown>
            <x-tiptapEditor.itemDropdown>
                <x-tiptapEditor.button
                    title="{{ __('Block styles') }}"
                    icon="pilcrow"
                    x-bind:disabled="isToolbarLocked()"
                />
                <x-tiptapEditor.items
                    x-show="isToolbarOpen && !isToolbarLocked()"
                    x-anchor.bottom="$refs.button"
                >
                    <li class="flex">
                        <x-tiptapEditor.subButton
                            title="{{ __('Paragraph') }}"
                            icon="pilcrow"
                            @click="editor.setParagraph()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Quote') }}"
                            icon="quote"
                            @click="editor.toggleBlockquote()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Code block') }}"
                            icon="code"
                            @click="editor.toggleCodeBlock()"
                        />
                    </li>
                </x-tiptapEditor.items>
            </x-tiptapEditor.itemDropdown>
            <x-tiptapEditor.itemDropdown>
                <x-tiptapEditor.button
                    title="{{ __('Text styles') }}"
                    icon="text"
                    x-bind:disabled="isToolbarLocked()"
                />
                <x-tiptapEditor.items
                    x-show="isToolbarOpen && !isToolbarLocked()"
                    x-anchor.bottom="$refs.button"
                >
                    <li class="flex">
                        <x-tiptapEditor.subButton
                            title="{{ __('Bold') }}"
                            icon="bold"
                            @click="editor.toggleBold()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Italic') }}"
                            icon="italic"
                            @click="editor.toggleItalic()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Strike') }}"
                            icon="strike"
                            @click="editor.toggleStrike()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Inline code') }}"
                            icon="codeInline"
                            @click="editor.toggleCode()"
                        />
                    </li>
                </x-tiptapEditor.items>
            </x-tiptapEditor.itemDropdown>
            <x-tiptapEditor.itemDropdown>
                <x-tiptapEditor.button
                    title="{{ __('Lists') }}"
                    icon="list"
                    x-bind:disabled="isToolbarLocked()"
                />
                <x-tiptapEditor.items
                    x-show="isToolbarOpen && !isToolbarLocked()"
                    x-anchor.bottom="$refs.button"
                >
                    <li class="flex">
                        <x-tiptapEditor.subButton
                            title="{{ __('List') }}"
                            icon="list"
                            @click="editor.toggleBulletList()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Ordered list') }}"
                            icon="listOrdered"
                            @click="editor.toggleOrderedList()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Task list') }}"
                            icon="listTodo"
                            @click="editor.toggleTaskList()"
                        />
                    </li>
                </x-tiptapEditor.items>
            </x-tiptapEditor.itemDropdown>
            <x-tiptapEditor.itemDropdown>
                <x-tiptapEditor.button
                    title="{{ __('Insert') }}"
                    icon="documentPlus"
                    x-bind:disabled="isToolbarLocked()"
                />
                <x-tiptapEditor.items
                    x-show="isToolbarOpen && !isToolbarLocked()"
                    x-anchor.bottom="$refs.button"
                >
                    <li class="flex">
                        <x-tiptapEditor.subButton
                            title="{{ __('Link') }}"
                            icon="link"
                            @click="$dispatch('toggle-mde-search-link-modal', { url: editor.getEditor().getAttributes('link').href ?? '' })"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Image') }}"
                            icon="image"
                            @click="$dispatch('toggle-mde-search-image-modal', { url: editor.getEditor().getAttributes('image').src ?? '' })"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Horizontal rule') }}"
                            icon="horizontalRule"
                            @click="editor.setHorizontalRule()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Template') }}"
                            icon="template"
                            @click="$wire.dispatchTo('modals.markdown-editor-template', 'open-modal', { selectedFile: $wire.selectedFileId })"
                        />
                    </li>
                </x-tiptapEditor.items>
            </x-tiptapEditor.itemDropdown>
            <x-tiptapEditor.itemDropdown>
                <x-tiptapEditor.button
                    title="{{ __('Tables') }}"
                    icon="tableAdd"
                    x-bind:disabled="isToolbarLocked()"
                />
                <x-tiptapEditor.items
                    x-show="isToolbarOpen && !isToolbarLocked()"
                    x-anchor.bottom="$refs.button"
                >
                    <li class="flex">
                        <x-tiptapEditor.subButton
                            title="{{ __('Insert table') }}"
                            icon="tableAdd"
                            @click="editor.insertTable()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Delete table') }}"
                            icon="tableDelete"
                            @click="editor.deleteTable()"
                        />
                    </li>
                    <x-tiptapEditor.itemDivider />
                    <li class="flex">
                        <x-tiptapEditor.subButton
                            title="{{ __('Add column before') }}"
                            icon="tableAddColumn"
                            iconRotate
                            @click="editor.addColumnBefore()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Add column after') }}"
                            icon="tableAddColumn"
                            @click="editor.addColumnAfter()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Delete column') }}"
                            icon="tableDeleteColumn"
                            @click="editor.deleteColumn()"
                        />
                    </li>
                    <li class="flex">
                        <x-tiptapEditor.subButton
                            title="{{ __('Add row before') }}"
                            icon="tableAddRow"
                            iconRotate
                            @click="editor.addRowBefore()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Add row after') }}"
                            icon="tableAddRow"
                            @click="editor.addRowAfter()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Delete row') }}"
                            icon="tableDeleteRow"
                            @click="editor.deleteRow()"
                        />
                    </li>
                    <x-tiptapEditor.itemDivider />
                    <li class="flex">
                        <x-tiptapEditor.subButton
                            title="{{ __('Align left') }}"
                            icon="alignLeft"
                            @click="editor.setTableColumnAlignmentLeft()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Align center') }}"
                            icon="alignCenter"
                            @click="editor.setTableColumnAlignmentCenter()"
                        />
                        <x-tiptapEditor.subButton
                            title="{{ __('Align right') }}"
                            icon="alignRight"
                            @click="editor.setTableColumnAlignmentRight()"
                        />
                    </li>
                </x-tiptapEditor.items>
            </x-tiptapEditor.itemDropdown>
        </ul>
    </div>
    <ul class="flex gap-1">
        <li>
            <x-tiptapEditor.button
                title="{{ __('Toggle Markdown') }}"
                icon="markdown"
                toggle="true"
                x-bind:class="isEditingMarkdown ? 'bg-primary-400 dark:bg-primary-500 text-light-base-50! dark:text-light-base-50!' : ''"
                @click="toggleMarkdown"
            />
        </li>
        <li>
            <x-tiptapEditor.button
                title="{{ __('Toggle editing') }}"
                icon="pencilOff"
                toggle="true"
                x-bind:class="!isEditMode ? 'bg-primary-400 dark:bg-primary-500 text-light-base-50! dark:text-light-base-50!' : ''"
                @click="toggleEditMode"
            />
        </li>
    </ul>
</div>
