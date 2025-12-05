<script setup lang="ts">
import PasswordController from '@/actions/App/Http/Controllers/PasswordController';
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
    createToast('Password updated', 'success');
};
</script>

<template>
    <Form
        v-slot="{ errors, processing }"
        v-bind="PasswordController.update.form()"
        class="flex flex-col gap-6 inert:pointer-events-none"
        autocomplete="off"
        novalidate
        disable-while-processing
        @success="handleSuccess"
    >
        <Input
            name="current_password"
            type="password"
            placeholder="Current password"
            :error="errors.current_password"
            required
            autofocus
        />
        <Input
            name="password"
            type="password"
            placeholder="New password"
            :error="errors.password"
            required
        />
        <Input
            name="password_confirmation"
            type="password"
            placeholder="Confirm password"
            :error="errors.password"
            required
        />
        <div class="flex justify-end gap-2 py-1">
            <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
            <Submit label="Save" :processing="processing" />
        </div>
    </Form>
</template>
