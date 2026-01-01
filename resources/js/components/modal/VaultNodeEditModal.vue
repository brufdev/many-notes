<script setup lang="ts">
import { update } from '@/actions/App/Http/Controllers/VaultNodeController';
import ModelInput from '@/components/form/ModelInput.vue';
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultNodeTreeItem } from '@/types/vault';

const { closeModal } = useModalManager();
const { createToast } = useToast();
const vaultTreeStore = useVaultTreeStore();

const props = defineProps<{
    id: number;
    vaultId: number;
    isFile: boolean;
    name: string;
}>();

const form = useAxiosForm<{ name: string }>({
    name: props.name,
});

const url = update.url({ vault: props.vaultId, node: props.id });

const handleSubmit = () => {
    form.send({
        url: url,
        method: 'patch',
        onError: error => {
            closeModal();
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: (response: { node: VaultNodeTreeItem }) => {
            closeModal();
            const message = props.isFile ? 'File updated' : 'Folder updated';
            createToast(message, 'success');
            vaultTreeStore.handleNodeUpdated(response.node);
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
