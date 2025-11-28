<script setup lang="ts">
import VaultController from '@/actions/App/Http/Controllers/VaultController';
import Input from '@/components/form/Input.vue';
import Submit from '@/components/form/Submit.vue';
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
    <div>
        <Form
            v-slot="{ errors, processing }"
            v-bind="VaultController.store.form()"
            class="flex flex-col gap-6"
            autocomplete="off"
            novalidate
            @success="handleSuccess"
        >
            <Input
                name="name"
                type="text"
                placeholder="Name"
                :error="errors.name"
                required
                autofocus
            />
            <Submit label="Create" :processing="processing" />
        </Form>
    </div>
</template>
