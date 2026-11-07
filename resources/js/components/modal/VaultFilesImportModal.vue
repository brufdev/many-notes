<script setup lang="ts">
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { useVaultFileUpload } from '@/composables/useVaultFileUpload';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    vaultId: number;
    parentId: number | null;
    dropEvent: DragEvent | null;
}>();

const page = usePage();
const { closeModal } = useModalManager();
const { createToast } = useToast();
const { form, filesError, tooLargeMessage, filterValidFiles, importFiles } = useVaultFileUpload();

const uploadMaxFilesize = computed(() => page.props.app?.metadata?.upload_max_filesize ?? '0');

const fileUpload = ref<HTMLInputElement | null>(null);

const drop = (event: DragEvent) => {
    if (!fileUpload.value || !event.dataTransfer) {
        return;
    }

    fileUpload.value.files = event.dataTransfer.files;
    handleSubmit();
};

const handleSubmit = () => {
    if (!fileUpload.value?.files) {
        return;
    }

    const files = filterValidFiles(Array.from(fileUpload.value.files));

    if (files.length === 0) {
        createToast(tooLargeMessage.value, 'error');

        return;
    }

    importFiles({
        vaultId: props.vaultId,
        parentId: props.parentId,
        files: files,
        onFinish: () => {
            if (!form.hasErrors) {
                closeModal();
            }
        },
    });
};

onMounted(() => {
    if (props.dropEvent) {
        drop(props.dropEvent);
    }
});
</script>

<template>
    <form
        class="flex flex-col gap-6 inert:pointer-events-none"
        autocomplete="off"
        novalidate
        :inert="form.processing"
        @submit.prevent="handleSubmit"
    >
        <div
            class="border-light-base-300 dark:border-base-500 flex h-48 w-full flex-col items-center justify-center rounded-lg border-2 border-dashed"
        >
            <label
                for="file-upload"
                class="flex h-full w-full cursor-pointer flex-col items-center justify-center gap-2 text-base font-medium"
                @drop.prevent="drop"
            >
                <h6 class="font-semibold">Drop or browse files to import</h6>
                <span class="text-sm">Image, video, audio, note or pdf files</span>
                <span class="text-sm">Up to {{ uploadMaxFilesize }}</span>

                <p v-if="filesError" class="text-error-500 text-sm">
                    {{ filesError }}
                </p>

                <progress
                    v-if="form.progress"
                    class="mt-2 h-1 w-64"
                    :value="form.progress.percentage"
                    max="100"
                >
                    {{ form.progress.percentage }}%
                </progress>
            </label>

            <input
                id="file-upload"
                ref="fileUpload"
                type="file"
                multiple
                class="hidden"
                @change="handleSubmit"
            />
        </div>
    </form>
</template>
