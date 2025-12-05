<script setup lang="ts">
import VaultImportController from '@/actions/App/Http/Controllers/VaultImportController';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { AppPageProps } from '@/types';
import { Form, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = usePage().props as AppPageProps;
const { closeModal } = useModalManager();
const { createToast } = useToast();

const uploadMaxFilesize = computed(() => props.app?.metadata?.upload_max_filesize);

const handleSuccess = () => {
    closeModal();
    createToast('Vault imported', 'success');
};
</script>

<template>
    <Form
        v-slot="{ errors, progress, submit }"
        v-bind="VaultImportController.form()"
        class="flex flex-col gap-6"
        autocomplete="off"
        novalidate
        disable-while-processing
        @success="handleSuccess"
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

                <p v-if="errors.file" class="text-error-500 text-sm">
                    {{ errors.file }}
                </p>

                <progress
                    v-if="progress"
                    class="mt-2 h-1 w-64"
                    :value="progress.percentage"
                    max="100"
                >
                    {{ progress.percentage }}%
                </progress>
            </label>

            <input
                id="file-upload"
                type="file"
                name="file"
                class="hidden"
                accept="application/zip"
                @change="submit"
            />
        </div>
    </Form>
</template>
