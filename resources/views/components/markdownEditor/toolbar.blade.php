<div {{ $attributes }} class="flex justify-between">
    <ul class="flex gap-1">
        <li>
            <x-markdownEditor.button x-bind:class="!isEditMode ? 'bg-light-base-400 dark:bg-base-700' : ''" @click="toggleEditMode">
                {{ __('Preview') }}
            </x-markdownEditor.button>
        </li>
    </ul>
    <div class="relative flex">
        <ul class="flex gap-1">
            <x-markdownEditor.itemDropdown>
                <x-markdownEditor.button>{{ __('Style') }}</x-markdownEditor.button>
                <x-markdownEditor.items x-show="isToolbarOpen" x-anchor.bottom="$refs.button">
                    <x-markdownEditor.subButton @click="unorderedList">{{ __('Unordered list') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="orderedList">{{ __('Ordered list') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="taskList">{{ __('Task list') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.itemDivider />
                    <x-markdownEditor.subButton @click="heading(1)">{{ __('Heading 1') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="heading(2)">{{ __('Heading 2') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="heading(3)">{{ __('Heading 3') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="heading(4)">{{ __('Heading 4') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="heading(5)">{{ __('Heading 5') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="heading(6)">{{ __('Heading 6') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.itemDivider />
                    <x-markdownEditor.subButton @click="blockquote">{{ __('Blockquote') }}</x-markdownEditor.subButton>
                </x-markdownEditor.items>
            </x-markdownEditor.itemDropdown>
            <x-markdownEditor.itemDropdown>
                <x-markdownEditor.button>{{ __('Format') }}</x-markdownEditor.button>
                <x-markdownEditor.items x-show="isToolbarOpen" x-anchor.bottom="$refs.button">
                    <x-markdownEditor.subButton @click="bold">{{ __('Bold') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="italic">{{ __('Italic') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="strikethrough">{{ __('Strikethrough') }}</x-markdownEditor.subButton>
                </x-markdownEditor.items>
            </x-markdownEditor.itemDropdown>
            <x-markdownEditor.itemDropdown>
                <x-markdownEditor.button>{{ __('Insert') }}</x-markdownEditor.button>
                <x-markdownEditor.items x-show="isToolbarOpen" x-anchor.bottom="$refs.button">
                    <x-markdownEditor.subButton @click="$wire.dispatchTo('modals.markdown-editor-search', 'open-modal')">{{ __('Link') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="link()">{{ __('External link') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="$wire.dispatchTo('modals.markdown-editor-search', 'open-modal', { type: 'image' })">{{ __('Image') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="image()">{{ __('External image') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="table">{{ __('Table') }}</x-markdownEditor.subButton>
                    <x-markdownEditor.subButton @click="$wire.dispatchTo('modals.markdown-editor-template', 'open-modal', { selectedFile: $wire.selectedFile })">{{ __('Template') }}</x-markdownEditor.subButton>
                </x-markdownEditor.items>
            </x-markdownEditor.itemDropdown>
        </ul>
        <div x-show="!isEditMode" class="absolute inset-0 opacity-25 bg-light-base-200 dark:bg-base-950"></div>
    </div>
</div>
