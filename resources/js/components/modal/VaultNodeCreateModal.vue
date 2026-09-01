<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/VaultNodeController';
import ModelInput from '@/components/form/ModelInput.vue';
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useRequest } from '@/composables/useRequest';
import { useScreenSize } from '@/composables/useScreenSize';
import { useToast } from '@/composables/useToast';
import { useVaultActions } from '@/composables/useVaultActions';
import { useLayoutStore } from '@/stores/layout';
import { useVaultRecentFileStore } from '@/stores/vaultRecentFile';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultNode } from '@/types/vault';

const props = defineProps<{
    vaultId: number;
    parentId: number | null;
    isFile: boolean;
}>();

const layoutStore = useLayoutStore();
const vaultRecentFileStore = useVaultRecentFileStore();
const vaultTreeStore = useVaultTreeStore();
const { closeModal } = useModalManager();
const { createToast } = useToast();
const { isSmallScreen } = useScreenSize();
const vaultActions = useVaultActions();

const form = useRequest<{
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
    form.post(url, {
        onFailure: () => closeModal(),
        onSuccess: (response: { data: VaultNode }) => {
            closeModal();
            const message = response.data.is_file ? 'File created' : 'Folder created';
            createToast(message, 'success');

            vaultTreeStore.handleNodeSaved(response.data);

            if (response.data.is_file) {
                vaultRecentFileStore.upsertRecentFile(response.data);

                if (isSmallScreen.value) {
                    layoutStore.closePanels();
                }

                vaultActions.openFile(response.data.id);
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
