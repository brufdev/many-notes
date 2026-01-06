<script setup lang="ts">
import VaultNodeImportController from '@/actions/App/Http/Controllers/VaultNodeImportController';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { AppPageProps } from '@/types';
import { VaultNodeTreeItem } from '@/types/vault';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage<AppPageProps>();
const { closeModal } = useModalManager();
const { createToast } = useToast();
const vaultTreeStore = useVaultTreeStore();

const props = defineProps<{
    vaultId: number;
    parentId: number | null;
}>();

const uploadMaxFilesize = computed(() => page.props.app?.metadata?.upload_max_filesize ?? '0');
const uploadMaxFilesizeBytes = computed(
    () => page.props.app?.metadata?.upload_max_filesize_bytes ?? 0
);
const uploadAllowedExtensions = computed(
    () => page.props.app?.metadata?.upload_allowed_extensions ?? ''
);

const fileUpload = ref<HTMLInputElement | null>(null);

const form = useAxiosForm<{
    parent_id: number | null;
    files: File[];
}>({
    parent_id: props.parentId,
    files: [],
});

const drop = (event: DragEvent) => {
    if (!fileUpload.value || !event.dataTransfer) {
        return;
    }

    fileUpload.value.files = event.dataTransfer.files;
    handleSubmit();
};

const handleSubmit = () => {
    if (!fileUpload.value || !fileUpload.value.files) {
        return;
    }

    const dataTransfer = new DataTransfer();

    for (const file of Array.from(fileUpload.value.files)) {
        const extension = file.name.split('.').pop()?.toLowerCase();
        const allowedExtensions = uploadAllowedExtensions.value
            .split(',')
            .map(ext => ext.replaceAll('.', ''));
        const invalidExtension = !extension || !allowedExtensions.includes(extension);
        const invalidSize = file.size > uploadMaxFilesizeBytes.value;

        if (invalidExtension || invalidSize) {
            continue;
        }

        dataTransfer.items.add(file);
    }

    if (dataTransfer.files.length === 0) {
        createToast('No valid files to import', 'error');

        return;
    }

    form.files = Array.from(dataTransfer.files);

    form.send({
        url: VaultNodeImportController.url({ vault: props.vaultId }),
        method: 'post',
        onError: error => {
            closeModal();
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: (response: { files: VaultNodeTreeItem[] }) => {
            closeModal();

            if (response.files.length === 0) {
                createToast('No files were imported', 'error');

                return;
            }

            const message = response.files.length === 1 ? 'file imported' : 'files imported';
            createToast(`${response.files.length} ${message}`, 'success');

            for (const file of response.files) {
                vaultTreeStore.handleNodeCreated(file);
            }
        },
    });
};
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

                <p v-if="form.errors.files" class="text-error-500 text-sm">
                    {{ form.errors.files }}
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
                :accept="uploadAllowedExtensions"
                multiple
                class="hidden"
                @change="handleSubmit"
            />
        </div>
    </form>
</template>
