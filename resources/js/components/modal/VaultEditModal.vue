<script setup lang="ts">
import { update } from '@/actions/App/Http/Controllers/VaultController';
import ModelInput from '@/components/form/ModelInput.vue';
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { VaultUpdated } from '@/types/vault.events';

const { closeModal } = useModalManager();
const { createToast } = useToast();

const props = defineProps<{
    id: number;
    name: string;
    onSuccess?: (vault: VaultUpdated) => void;
}>();

const form = useAxiosForm<{ name: string }>({
    name: props.name,
});

const url = update.url({ vault: props.id });

const handleSubmit = () => {
    form.send({
        url: url,
        method: 'patch',
        onError: error => {
            closeModal();
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: (response: { data: VaultUpdated }) => {
            closeModal();
            createToast('Vault updated', 'success');
            props.onSuccess?.(response.data);
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
