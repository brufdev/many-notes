<script setup lang="ts">
import VaultEditorApplyTemplateController from '@/actions/App/Http/Controllers/VaultEditorApplyTemplateController';
import Menu from '@/components/menu/Menu.vue';
import VaultEditorSearchFileModal from '@/components/modal/VaultEditorSearchModal.vue';
import VaultEditorTemplateListModal from '@/components/modal/VaultEditorTemplateListModal.vue';
import { useEditor } from '@/composables/useEditor';
import { useModalManager } from '@/composables/useModalManager';
import { useRequest } from '@/composables/useRequest';
import { useTiptapPreferences } from '@/composables/useTiptapPreferences';
import AlignCenter from '@/icons/AlignCenter.vue';
import AlignLeft from '@/icons/AlignLeft.vue';
import AlignRight from '@/icons/AlignRight.vue';
import Bold from '@/icons/Bold.vue';
import Code from '@/icons/Code.vue';
import CodeInline from '@/icons/CodeInline.vue';
import DocumentPlus from '@/icons/DocumentPlus.vue';
import Heading from '@/icons/Heading.vue';
import Heading1 from '@/icons/Heading1.vue';
import Heading2 from '@/icons/Heading2.vue';
import Heading3 from '@/icons/Heading3.vue';
import Heading4 from '@/icons/Heading4.vue';
import Heading5 from '@/icons/Heading5.vue';
import Heading6 from '@/icons/Heading6.vue';
import HorizontalRule from '@/icons/HorizontalRule.vue';
import Image from '@/icons/Image.vue';
import Indent from '@/icons/Indent.vue';
import Italic from '@/icons/Italic.vue';
import Link from '@/icons/Link.vue';
import List from '@/icons/List.vue';
import ListOrdered from '@/icons/ListOrdered.vue';
import ListTodo from '@/icons/ListTodo.vue';
import Markdown from '@/icons/Markdown.vue';
import Outdent from '@/icons/Outdent.vue';
import PencilOff from '@/icons/PencilOff.vue';
import Pilcrow from '@/icons/Pilcrow.vue';
import Print from '@/icons/Print.vue';
import Quote from '@/icons/Quote.vue';
import Redo from '@/icons/Redo.vue';
import Strike from '@/icons/Strike.vue';
import TableAdd from '@/icons/TableAdd.vue';
import TableAddColumn from '@/icons/TableAddColumn.vue';
import TableAddRow from '@/icons/TableAddRow.vue';
import TableDelete from '@/icons/TableDelete.vue';
import TableDeleteColumn from '@/icons/TableDeleteColumn.vue';
import TableDeleteRow from '@/icons/TableDeleteRow.vue';
import Template from '@/icons/Template.vue';
import Text from '@/icons/Text.vue';
import Undo from '@/icons/Undo.vue';
import { useLayoutStore } from '@/stores/layout';
import { computed, inject, type ShallowRef } from 'vue';
import MarkdownToolbarButton from './MarkdownToolbarButton.vue';
import MarkdownToolbarItemDivider from './MarkdownToolbarItemDivider.vue';
import MarkdownToolbarSubButton from './MarkdownToolbarSubButton.vue';

const props = defineProps<{
    vaultId: number;
    nodeId: number;
}>();

const editorContext = inject<ShallowRef<ReturnType<typeof useEditor> | null>>('editorContext');

const layoutStore = useLayoutStore();
const { openModal } = useModalManager();
const { isEditMode, isEditingMarkdown, toggleEditMode, toggleEditingMarkdown } =
    useTiptapPreferences();

const applyTemplateForm = useRequest<{ node_id: number }>({ node_id: props.nodeId });

const editor = computed(() => editorContext?.value?.editor.value ?? null);

const isToolbarLocked = computed(() => !isEditMode.value || isEditingMarkdown.value);

function undo(): void {
    editor.value?.chain().focus().undo().run();
}

function redo(): void {
    editor.value?.chain().focus().redo().run();
}

function toggleHeading(level: 1 | 2 | 3 | 4 | 5 | 6): void {
    editor.value?.chain().focus().toggleHeading({ level: level }).run();
}

function setParagraph(): void {
    editor.value?.chain().focus().setParagraph().run();
}

function toggleBlockquote(): void {
    editor.value?.chain().focus().toggleBlockquote().run();
}

function toggleCodeBlock(): void {
    editor.value?.chain().focus().toggleCodeBlock().run();
}

function toggleBold(): void {
    editor.value?.chain().focus().toggleBold().run();
}

function toggleItalic(): void {
    editor.value?.chain().focus().toggleItalic().run();
}

function toggleStrike(): void {
    editor.value?.chain().focus().toggleStrike().run();
}

function toggleCode(): void {
    editor.value?.chain().focus().toggleCode().run();
}

function toggleBulletList(): void {
    editor.value?.chain().focus().toggleBulletList().run();
}

function toggleOrderedList(): void {
    editor.value?.chain().focus().toggleOrderedList().run();
}

function toggleTaskList(): void {
    editor.value?.chain().focus().toggleTaskList().run();
}

function indentList(): void {
    const instance = editor.value;

    if (!instance) {
        return;
    }

    if (instance.can().sinkListItem('listItem')) {
        instance.chain().focus().sinkListItem('listItem').run();
    } else if (instance.can().sinkListItem('taskItem')) {
        instance.chain().focus().sinkListItem('taskItem').run();
    }
}

function outdentList(): void {
    const instance = editor.value;

    if (!instance) {
        return;
    }

    if (instance.can().liftListItem('listItem')) {
        instance.chain().focus().liftListItem('listItem').run();
    } else if (instance.can().liftListItem('taskItem')) {
        instance.chain().focus().liftListItem('taskItem').run();
    }
}

function openSearchFileModal(): void {
    openModal(VaultEditorSearchFileModal, {
        title: 'Insert link',
        top: true,
        initialUrl: editor.value?.getAttributes('link').href ?? '',
        onSelect: (url: string, name: string) => toggleLink(url, name),
    });
}

function toggleLink(url: string, name: string): void {
    const instance = editor.value;

    if (!instance) {
        return;
    }

    if (url === '') {
        instance.chain().focus().extendMarkRange('link').unsetLink().run();

        return;
    }

    const isEmptySelection = instance.state.selection.empty;

    if (isEmptySelection && !instance.isActive('link')) {
        const content = {
            type: 'text',
            text: name || url,
            marks: [{ type: 'link', attrs: { href: url } }],
        };

        instance.chain().focus().insertContent(content).run();

        return;
    }

    instance.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
}

function openSearchImageModal(): void {
    openModal(VaultEditorSearchFileModal, {
        title: 'Insert image',
        top: true,
        searchType: 'image',
        initialUrl: editor.value?.getAttributes('image').src ?? '',
        onSelect: (url: string, name: string) => setImage(url, name),
    });
}

function setImage(url: string, name: string): void {
    const instance = editor.value;

    if (!instance) {
        return;
    }

    if (url === '') {
        return;
    }

    const { from, to } = instance.state.selection;
    const selectedText = instance.state.doc.textBetween(from, to, ' ');
    const options = { src: url, alt: selectedText || name, title: '' };

    instance.chain().focus().setImage(options).run();
}

function setHorizontalRule(): void {
    editor.value?.chain().focus().setHorizontalRule().run();
}

function openTemplateListModal(): void {
    openModal(VaultEditorTemplateListModal, {
        title: 'Choose a template',
        top: true,
        onSelect: (templateId: number) => applyTemplateToVaultNode(templateId),
    });
}

function applyTemplateToVaultNode(templateId: number): void {
    const url = VaultEditorApplyTemplateController.url({
        vault: props.vaultId,
        template: templateId,
    });

    layoutStore.setAppLoading(true);

    applyTemplateForm.node_id = props.nodeId;

    applyTemplateForm.post(url, {
        onFinish: () => {
            layoutStore.setAppLoading(false);
        },
    });
}

function insertTable(): void {
    editor.value?.chain().focus().insertTable().run();
}

function deleteTable(): void {
    editor.value?.chain().focus().deleteTable().run();
}

function addColumnBefore(): void {
    editor.value?.chain().focus().addColumnBefore().run();
}

function addColumnAfter(): void {
    editor.value?.chain().focus().addColumnAfter().run();
}

function deleteColumn(): void {
    editor.value?.chain().focus().deleteColumn().run();
}

function addRowBefore(): void {
    editor.value?.chain().focus().addRowBefore().run();
}

function addRowAfter(): void {
    editor.value?.chain().focus().addRowAfter().run();
}

function deleteRow(): void {
    editor.value?.chain().focus().deleteRow().run();
}

function setTableColumnAlignment(alignment: 'left' | 'center' | 'right'): void {
    editor.value?.chain().focus().setTableColumnAlignment(alignment).run();
}

function print(): void {
    globalThis.print();
}
</script>

<template>
    <div class="flex justify-between gap-1">
        <div class="scrollbar-hidden overflow-x-auto">
            <div class="flex gap-1">
                <MarkdownToolbarButton
                    title="Undo"
                    :icon="Undo"
                    :disabled="isToolbarLocked"
                    @click="undo"
                />
                <MarkdownToolbarButton
                    title="Redo"
                    :icon="Redo"
                    :disabled="isToolbarLocked"
                    @click="redo"
                />
                <Menu type="hover" :disabled="isToolbarLocked">
                    <template #trigger>
                        <MarkdownToolbarButton
                            title="Heading"
                            :icon="Heading"
                            :disabled="isToolbarLocked"
                        />
                    </template>
                    <div class="flex">
                        <MarkdownToolbarSubButton
                            :icon="Heading1"
                            title="Heading 1"
                            @click="toggleHeading(1)"
                        />
                        <MarkdownToolbarSubButton
                            :icon="Heading2"
                            title="Heading 2"
                            @click="toggleHeading(2)"
                        />
                        <MarkdownToolbarSubButton
                            :icon="Heading3"
                            title="Heading 3"
                            @click="toggleHeading(3)"
                        />
                    </div>
                    <div class="flex">
                        <MarkdownToolbarSubButton
                            :icon="Heading4"
                            title="Heading 4"
                            @click="toggleHeading(4)"
                        />
                        <MarkdownToolbarSubButton
                            :icon="Heading5"
                            title="Heading 5"
                            @click="toggleHeading(5)"
                        />
                        <MarkdownToolbarSubButton
                            :icon="Heading6"
                            title="Heading 6"
                            @click="toggleHeading(6)"
                        />
                    </div>
                </Menu>
                <Menu type="hover" :disabled="isToolbarLocked">
                    <template #trigger>
                        <MarkdownToolbarButton
                            :icon="Pilcrow"
                            title="Block styles"
                            :disabled="isToolbarLocked"
                        />
                    </template>
                    <div class="flex">
                        <MarkdownToolbarSubButton
                            :icon="Pilcrow"
                            title="Paragraph"
                            @click="setParagraph"
                        />
                        <MarkdownToolbarSubButton
                            :icon="Quote"
                            title="Quote"
                            @click="toggleBlockquote"
                        />
                        <MarkdownToolbarSubButton
                            :icon="Code"
                            title="Code block"
                            @click="toggleCodeBlock"
                        />
                    </div>
                </Menu>
                <Menu type="hover" :disabled="isToolbarLocked">
                    <template #trigger>
                        <MarkdownToolbarButton
                            :icon="Text"
                            title="Text styles"
                            :disabled="isToolbarLocked"
                        />
                    </template>
                    <div class="flex">
                        <MarkdownToolbarSubButton :icon="Bold" title="Bold" @click="toggleBold" />
                        <MarkdownToolbarSubButton
                            :icon="Italic"
                            title="Italic"
                            @click="toggleItalic"
                        />
                        <MarkdownToolbarSubButton
                            :icon="Strike"
                            title="Strike"
                            @click="toggleStrike"
                        />
                        <MarkdownToolbarSubButton
                            :icon="CodeInline"
                            title="Inline code"
                            @click="toggleCode"
                        />
                    </div>
                </Menu>
                <Menu type="hover" :disabled="isToolbarLocked">
                    <template #trigger>
                        <MarkdownToolbarButton
                            :icon="List"
                            title="Lists"
                            :disabled="isToolbarLocked"
                        />
                    </template>
                    <div class="flex">
                        <MarkdownToolbarSubButton
                            :icon="List"
                            title="List"
                            @click="toggleBulletList"
                        />
                        <MarkdownToolbarSubButton
                            :icon="ListOrdered"
                            title="Ordered list"
                            @click="toggleOrderedList"
                        />
                        <MarkdownToolbarSubButton
                            :icon="ListTodo"
                            title="Task list"
                            @click="toggleTaskList"
                        />
                    </div>
                    <div class="flex">
                        <MarkdownToolbarSubButton
                            :icon="Indent"
                            title="Indent"
                            @click="indentList"
                        />
                        <MarkdownToolbarSubButton
                            :icon="Outdent"
                            title="Outdent"
                            @click="outdentList"
                        />
                    </div>
                </Menu>
                <Menu type="hover" :disabled="isToolbarLocked">
                    <template #trigger>
                        <MarkdownToolbarButton
                            :icon="DocumentPlus"
                            title="Insert"
                            :disabled="isToolbarLocked"
                        />
                    </template>
                    <div class="flex">
                        <MarkdownToolbarSubButton
                            :icon="Link"
                            title="Link"
                            @click="openSearchFileModal"
                        />
                        <MarkdownToolbarSubButton
                            :icon="Image"
                            title="Image"
                            @click="openSearchImageModal"
                        />
                        <MarkdownToolbarSubButton
                            :icon="HorizontalRule"
                            title="Horizontal rule"
                            @click="setHorizontalRule"
                        />
                        <MarkdownToolbarSubButton
                            :icon="Template"
                            title="Template"
                            @click="openTemplateListModal"
                        />
                    </div>
                </Menu>
                <Menu type="hover" :disabled="isToolbarLocked">
                    <template #trigger>
                        <MarkdownToolbarButton
                            title="Tables"
                            :icon="TableAdd"
                            :disabled="isToolbarLocked"
                        />
                    </template>
                    <div class="flex">
                        <MarkdownToolbarSubButton
                            :icon="TableAdd"
                            title="Insert table"
                            @click="insertTable"
                        />
                        <MarkdownToolbarSubButton
                            :icon="TableDelete"
                            title="Delete table"
                            @click="deleteTable"
                        />
                    </div>
                    <MarkdownToolbarItemDivider />
                    <div class="flex">
                        <MarkdownToolbarSubButton
                            :icon="TableAddColumn"
                            :icon-rotate="true"
                            title="Add column before"
                            @click="addColumnBefore"
                        />
                        <MarkdownToolbarSubButton
                            :icon="TableAddColumn"
                            title="Add column after"
                            @click="addColumnAfter"
                        />
                        <MarkdownToolbarSubButton
                            :icon="TableDeleteColumn"
                            title="Delete column"
                            @click="deleteColumn"
                        />
                    </div>
                    <div class="flex">
                        <MarkdownToolbarSubButton
                            :icon="TableAddRow"
                            :icon-rotate="true"
                            title="Add row before"
                            @click="addRowBefore"
                        />
                        <MarkdownToolbarSubButton
                            :icon="TableAddRow"
                            title="Add row after"
                            @click="addRowAfter"
                        />
                        <MarkdownToolbarSubButton
                            :icon="TableDeleteRow"
                            title="Delete row"
                            @click="deleteRow"
                        />
                    </div>
                    <MarkdownToolbarItemDivider />
                    <div class="flex">
                        <MarkdownToolbarSubButton
                            :icon="AlignLeft"
                            title="Align left"
                            @click="setTableColumnAlignment('left')"
                        />
                        <MarkdownToolbarSubButton
                            :icon="AlignCenter"
                            title="Align center"
                            @click="setTableColumnAlignment('center')"
                        />
                        <MarkdownToolbarSubButton
                            :icon="AlignRight"
                            title="Align right"
                            @click="setTableColumnAlignment('right')"
                        />
                    </div>
                </Menu>
            </div>
        </div>
        <div class="flex gap-1">
            <MarkdownToolbarButton
                title="Toggle Markdown"
                :icon="Markdown"
                :toggle="true"
                :class="
                    isEditingMarkdown
                        ? 'bg-primary-400 dark:bg-primary-500 text-light-base-50! dark:text-light-base-50!'
                        : ''
                "
                @click="toggleEditingMarkdown"
            />
            <MarkdownToolbarButton
                title="Toggle editing"
                :icon="PencilOff"
                :toggle="true"
                :class="
                    !isEditMode
                        ? 'bg-primary-400 dark:bg-primary-500 text-light-base-50! dark:text-light-base-50!'
                        : ''
                "
                @click="toggleEditMode"
            />
            <MarkdownToolbarButton title="Print" :icon="Print" @click="print" />
        </div>
    </div>
</template>
