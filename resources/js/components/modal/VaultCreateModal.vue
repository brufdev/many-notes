<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/VaultController';
import ModelInput from '@/components/form/ModelInput.vue';
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useRequest } from '@/composables/useRequest';
import { useToast } from '@/composables/useToast';

const { closeModal } = useModalManager();
const { createToast } = useToast();

const form = useRequest<{ name: string }>({ name: '' });

const url = store.url();

const handleSubmit = () => {
    form.post(url, {
        onFailure: () => closeModal(),
        onSuccess: () => {
            closeModal();
            createToast('Vault created', 'success');
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
            v-model="form.name"
            name="name"
            type="text"
            placeholder="Name"
            :error="form.errors.name"
            required
            autofocus
        />
        <div class="flex justify-end gap-2 py-1">
            <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
            <Submit label="Save" :processing="form.processing" />
        </div>
    </form>
</template>
