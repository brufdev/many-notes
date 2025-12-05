<script setup lang="ts">
import VaultController from '@/actions/App/Http/Controllers/VaultController';
import Input from '@/components/form/Input.vue';
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { Form } from '@inertiajs/vue3';

const { closeModal } = useModalManager();
const { createToast } = useToast();

const handleSuccess = () => {
    closeModal();
    createToast('Vault created', 'success');
};
</script>

<template>
    <Form
        v-slot="{ errors, processing }"
        v-bind="VaultController.store.form()"
        class="flex flex-col gap-6 inert:pointer-events-none"
        autocomplete="off"
        novalidate
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
