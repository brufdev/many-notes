<script setup lang="ts">
import { update } from '@/actions/App/Http/Controllers/VaultNodeController';
import { ModelInput, Submit } from '@/components/form';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useModalManager, useRequest, useScreenSize, useToast } from '@/composables';
import { useLayoutStore } from '@/stores/layout';
import { useVaultRecentFileStore } from '@/stores/vaultRecentFile';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultNode } from '@/types/vault';
import { VaultShowPageProps } from '@/types/vault.pages';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    id: number;
    vaultId: number;
    isFile: boolean;
    name: string;
}>();

const page = usePage<VaultShowPageProps>();

const layoutStore = useLayoutStore();
const vaultRecentFileStore = useVaultRecentFileStore();
const vaultTreeStore = useVaultTreeStore();
const { closeModal } = useModalManager();
const { createToast } = useToast();
const { isSmallScreen } = useScreenSize();

const form = useRequest<{ name: string }>({ name: props.name });

const url = update.url({ vault: props.vaultId, node: props.id });

const handleSubmit = () => {
    form.patch(url, {
        onFailure: () => closeModal(),
        onSuccess: (response: { data: VaultNode }) => {
            closeModal();
            const message = props.isFile ? 'File updated' : 'Folder updated';
            createToast(message, 'success');

            if (isSmallScreen.value) {
                layoutStore.closePanels();
            }

            vaultTreeStore.handleNodeSaved(response.data);

            if (response.data.is_file) {
                vaultRecentFileStore.upsertRecentFile(response.data);
            }

            if (page.props.openedFile?.file.id === response.data.id) {
                page.props.openedFile.file.name = response.data.name;
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
