<script setup lang="ts">
import MarkdownToolbarButton from '@/components/editor/MarkdownToolbarButton.vue';
import { useEditor } from '@/composables/useEditor';
import CheckCircle from '@/icons/CheckCircle.vue';
import DocumentDuplicate from '@/icons/DocumentDuplicate.vue';
import Print from '@/icons/Print.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { formatExtendedDate } from '@/utils/time';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

interface ShareShowPageProps {
    share: {
        token: string;
        name: string;
        content: string | null;
        updated_at: string | null;
    };
}

const props = defineProps<ShareShowPageProps>();

const noteEditorRef = ref<HTMLElement | null>(null);
const noteMarkdownRef = ref<HTMLElement | null>(null);
const isCopied = ref(false);

let copiedTimeout: ReturnType<typeof setTimeout> | null = null;

useEditor({
    vaultId: '',
    element: noteEditorRef,
    markdownElement: noteMarkdownRef,
    content: props.share.content ?? '',
    isEditMode: ref(false),
    imageBaseUrl: `/share/${props.share.token}/files`,
    onUpdate: () => {
        //
    },
    openFilePath: () => {
        //
    },
});

function print(): void {
    globalThis.print();
}

async function copyMarkdown(): Promise<void> {
    await navigator.clipboard.writeText(props.share.content ?? '');

    isCopied.value = true;

    if (copiedTimeout) {
        clearTimeout(copiedTimeout);
    }

    copiedTimeout = setTimeout(() => {
        isCopied.value = false;
    }, 1000);
}
</script>

<template>
    <PublicLayout>
        <Head :title="share.name" />

        <div class="flex items-start justify-between gap-4 pb-6">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-semibold break-words">{{ share.name }}</h1>
                <p v-if="share.updated_at" class="text-sm opacity-60">
                    Last updated {{ formatExtendedDate(share.updated_at) }}
                </p>
            </div>

            <div class="flex shrink-0 gap-1 print:hidden">
                <MarkdownToolbarButton title="Print" :icon="Print" @click="print" />
                <MarkdownToolbarButton
                    :title="isCopied ? 'Copied!' : 'Copy markdown'"
                    :icon="isCopied ? CheckCircle : DocumentDuplicate"
                    @click="copyMarkdown"
                />
            </div>
        </div>

        <div ref="noteEditorRef" class="w-full" spellcheck="false"></div>
        <div ref="noteMarkdownRef" class="hidden"></div>
    </PublicLayout>
</template>
