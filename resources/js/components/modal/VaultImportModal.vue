<script setup lang="ts">
import VaultImportController from '@/actions/App/Http/Controllers/VaultImportController';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { AppPageProps } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage<AppPageProps>();
const { closeModal } = useModalManager();
const { createToast } = useToast();

const uploadMaxFilesize = computed(() => page.props.app?.metadata?.upload_max_filesize ?? '0');
const uploadMaxFilesizeBytes = computed(
    () => page.props.app?.metadata?.upload_max_filesize_bytes ?? 0
);

const fileUpload = ref<HTMLInputElement | null>(null);

const form = useAxiosForm<{
    file: File | null;
}>({
    file: null,
});

const handleSubmit = () => {
    if (!fileUpload.value || !fileUpload.value.files) {
        return;
    }

    const file = fileUpload.value.files[0];

    const extension = file.name.split('.').pop()?.toLowerCase();
    const invalidExtension = !extension || extension !== 'zip';
    const invalidSize = file.size > uploadMaxFilesizeBytes.value;

    if (invalidExtension || invalidSize) {
        createToast('The file is not valid', 'error');

        return;
    }

    form.file = file;

    form.send({
        url: VaultImportController.url(),
        method: 'post',
        onError: error => {
            closeModal();
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: () => {
            closeModal();
            createToast('Vault imported', 'success');
            router.reload({ only: ['visibleVaults'] });
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
            >
                <h6 class="font-semibold">Browse file to import</h6>
                <span class="text-sm">ZIP files up to {{ uploadMaxFilesize }}</span>

                <p v-if="form.errors.file" class="text-error-500 text-sm">
                    {{ form.errors.file }}
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
                accept="application/zip"
                class="hidden"
                @change="handleSubmit"
            />
        </div>
    </form>
</template>
