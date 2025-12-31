<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/VaultNodeController';
import Input from '@/components/form/Input.vue';
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { useVaultTreeActions } from '@/composables/useVaultTreeActions';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultNodeTreeItem } from '@/types/vault';

const { closeModal } = useModalManager();
const { createToast } = useToast();
const vaultTreeStore = useVaultTreeStore();
const vaultTreeActions = useVaultTreeActions();

const props = defineProps<{
    vaultId: number;
    parentId: number | null;
    isFile: boolean;
}>();

const form = useAxiosForm<{
    parent_id: number | null;
    is_file: boolean;
    name: string;
}>({
    parent_id: props.parentId,
    is_file: props.isFile,
    name: '',
});

const url = store.url({ vault: props.vaultId });

const handleSubmit = () => {
    form.send({
        url: url,
        method: 'post',
        onError: error => {
            closeModal();
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: (response: { node: VaultNodeTreeItem }) => {
            closeModal();
            const message = props.isFile ? 'File created' : 'Folder created';
            createToast(message, 'success');
            vaultTreeStore.handleNodeCreated(response.node);

            if (props.isFile) {
                vaultTreeActions.openFile(response.node.id);
            }
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
        <Input
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
