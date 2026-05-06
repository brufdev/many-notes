<script setup lang="ts">
import VaultNodeController from '@/actions/App/Http/Controllers/VaultNodeController';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useEditor } from '@/composables/useEditor';
import { useTiptapPreferences } from '@/composables/useTiptapPreferences';
import { useToast } from '@/composables/useToast';
import { useVaultActions } from '@/composables/useVaultActions';
import { useLayoutStore } from '@/stores/layout';
import { VaultNode } from '@/types/vault';
import { inject, onMounted, ref, ShallowRef } from 'vue';

const props = defineProps<{
    node: VaultNode;
}>();

const layoutStore = useLayoutStore();
const { createToast } = useToast();
const vaultActions = useVaultActions();
const { isEditMode, isEditingMarkdown } = useTiptapPreferences();
const form = useAxiosForm({});

const editorContext = inject<ShallowRef<ReturnType<typeof useEditor> | null>>('editorContext');

if (!editorContext) {
    throw new Error('editorContext is not provided');
}

const noteEditorRef = ref<HTMLElement | null>(null);
const noteMarkdownRef = ref<HTMLElement | null>(null);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const { editor, setContent } = useEditor({
    vaultId: String(props.node.vault_id),
    element: noteEditorRef,
    markdownElement: noteMarkdownRef,
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

            form.send({
                url: url,
                method: 'patch',
                data: {
                    content: markdown,
                },
                onError: error => {
                    const message = error.response?.statusText ?? 'Something went wrong';
                    createToast(message, 'error');
                },
                onFinish: () => {
                    layoutStore.setVaultNodeUpdating(false);
                },
            });
        }, 1000);
    },
    openFilePath: vaultActions.openFilePath,
});

onMounted(() => {
    editorContext.value = { editor, setContent };
});
</script>

<template>
    <div class="flex h-full w-full flex-col">
        <div
            ref="noteEditorRef"
            class="h-full w-full px-4"
            :class="isEditingMarkdown ? 'hidden' : ''"
            spellcheck="false"
        ></div>

        <div
            ref="noteMarkdownRef"
            class="h-full w-full px-4 whitespace-pre-wrap focus:outline-none"
            :class="isEditingMarkdown ? '' : 'hidden'"
            :contenteditable="isEditMode ? 'plaintext-only' : 'false'"
            spellcheck="false"
            @input="editorContext?.setContent(noteMarkdownRef?.textContent ?? '')"
        />
    </div>
</template>
