<script setup lang="ts">
import { update } from '@/actions/App/Http/Controllers/PasswordController';
import { ModelInput, Submit } from '@/components/form';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useModalManager, useRequest, useToast } from '@/composables';

const { closeModal } = useModalManager();
const { createToast } = useToast();

const form = useRequest<{
    current_password: string;
    password: string;
    password_confirmation: string;
}>({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const handleSubmit = () => {
    form.patch(update.url(), {
        onFailure: () => closeModal(),
        onSuccess: () => {
            closeModal();
            createToast('Password updated', 'success');
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
        <ModelInput
            v-model="form.current_password"
            name="current_password"
            type="password"
            placeholder="Current password"
            :error="form.errors.current_password"
            required
            autofocus
        />
        <ModelInput
            v-model="form.password"
            name="password"
            type="password"
            placeholder="New password"
            :error="form.errors.password"
            required
        />
        <ModelInput
            v-model="form.password_confirmation"
            name="password_confirmation"
            type="password"
            placeholder="Confirm password"
            :error="form.errors.password_confirmation"
            required
        />
        <div class="flex justify-end gap-2 py-1">
            <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
            <Submit label="Save" :processing="form.processing" />
        </div>
    </form>
</template>
