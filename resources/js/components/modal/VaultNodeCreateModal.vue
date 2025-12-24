<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/VaultNodeController';
import Input from '@/components/form/Input.vue';
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { Form } from '@inertiajs/vue3';

const { closeModal } = useModalManager();
const { createToast } = useToast();

const props = defineProps<{
    vaultId: number;
    parentId: number | null;
    isFile: boolean;
}>();

const handleSuccess = () => {
    closeModal();
    const message = props.isFile ? 'File created' : 'Folder created';
    createToast(message, 'success');
};
</script>

<template>
    <Form
        v-slot="{ errors, processing }"
        v-bind="store.form({ vault: vaultId })"
        class="flex flex-col gap-6 inert:pointer-events-none"
        autocomplete="off"
        novalidate
        :transform="
            data => ({
                ...data,
                parent_id: parentId,
                is_file: isFile,
            })
        "
        disable-while-processing
        @success="handleSuccess"
    >
        <Input name="name" type="text" placeholder="Name" :error="errors.name" required autofocus />
        <div class="flex justify-end gap-2 py-1">
            <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
            <Submit label="Save" :processing="processing" />
        </div>
    </Form>
</template>
