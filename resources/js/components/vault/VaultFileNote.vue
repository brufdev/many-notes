<script setup lang="ts">
import VaultNodeController from '@/actions/App/Http/Controllers/VaultNodeController';
import {
    useEditor,
    useRequest,
    useTiptapPreferences,
    useVaultActions,
    useVaultFileUpload,
} from '@/composables';
import { useLayoutStore } from '@/stores/layout';
import { VaultNode } from '@/types/vault';
import { inject, onMounted, onUnmounted, ref, ShallowRef } from 'vue';

interface VaultFileNodeProps {
    node: VaultNode;
}

interface VaultFileNodeEmits {
    contentUpdated: [content: string];
}

const props = defineProps<VaultFileNodeProps>();
const emit = defineEmits<VaultFileNodeEmits>();

const editorContext = inject<ShallowRef<ReturnType<typeof useEditor> | null>>('editorContext');

if (!editorContext) {
    throw new Error('editorContext is not provided');
}

const layoutStore = useLayoutStore();
const vaultActions = useVaultActions();
const { importFiles } = useVaultFileUpload();
const { isEditMode, isEditingMarkdown, isSpellcheckEnabled } = useTiptapPreferences();

const form = useRequest<{ content: string }>({ content: props.node.content ?? '' });

const noteEditorRef = ref<HTMLElement | null>(null);
const noteMarkdownRef = ref<HTMLElement | null>(null);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;
let isUnmounted = false;

const { editor, setContent, onMarkdownChanged } = useEditor({
    vaultId: String(props.node.vault_id),
    element: noteEditorRef,
    markdownElement: noteMarkdownRef,
    autofocus: false,
    content: props.node.content ?? '',
    isEditMode: isEditMode,
    onUpdate: (markdown: string) => {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        debounceTimer = setTimeout(() => {
            const url = VaultNodeController.update.url({
                vault: props.node.vault_id,
                node: props.node.id,
            });

            layoutStore.setVaultNodeUpdating(true);

            form.content = markdown;

            form.patch(url, {
                onFailure: () => {
                    if (isUnmounted) {
                        return;
                    }

                    emit('contentUpdated', props.node.content ?? '');
                    editorContext.value?.setContent(props.node.content ?? '');
                },
                onSuccess: (response: { data: VaultNode }) => {
                    emit('contentUpdated', response.data.content ?? '');
                },
                onFinish: () => {
                    layoutStore.setVaultNodeUpdating(false);
                },
            });
        }, 1000);
    },
    openFilePath: vaultActions.openFilePath,
    uploadFiles: request =>
        importFiles({
            vaultId: props.node.vault_id,
            parentId: props.node.parent_id,
            files: request.files,
            onSuccess: request.onSuccess,
            onFinish: request.onFinish,
        }),
});

onMounted(() => {
    editorContext.value = { editor, setContent, onMarkdownChanged };
});

onUnmounted(() => {
    isUnmounted = true;
});
</script>

<template>
    <div class="flex h-full w-full flex-col">
        <div
            ref="noteEditorRef"
            class="h-full px-4"
            :class="isEditingMarkdown ? 'hidden' : ''"
            :spellcheck="isSpellcheckEnabled"
        ></div>

        <div
            ref="noteMarkdownRef"
            class="h-full px-4 break-words whitespace-break-spaces focus:outline-none"
            :class="isEditingMarkdown ? '' : 'hidden'"
            :contenteditable="isEditMode ? 'plaintext-only' : 'false'"
            :spellcheck="isSpellcheckEnabled"
            @input="editorContext?.onMarkdownChanged(noteMarkdownRef?.textContent ?? '')"
        />
    </div>
</template>
