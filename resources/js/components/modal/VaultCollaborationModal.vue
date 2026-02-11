<script setup lang="ts">
import { destroy, store } from '@/actions/App/Http/Controllers/VaultCollaborationController';
import ModelInput from '@/components/form/ModelInput.vue';
import Submit from '@/components/form/Submit.vue';
import Tab from '@/components/tabs/Tab.vue';
import TabPanel from '@/components/tabs/TabPanel.vue';
import Tabs from '@/components/tabs/Tabs.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import Trash from '@/icons/Trash.vue';
import { useVaultStore } from '@/stores/vault';
import { VaultCollaborator } from '@/types/vault';
import { ref } from 'vue';
import AxiosFormConfirmationModal from './AxiosFormConfirmationModal.vue';

const { openModal } = useModalManager();
const { createToast } = useToast();
const vaultStore = useVaultStore();

const props = defineProps<{
    vaultId: number;
}>();

const activeTab = ref('users');

const form = useAxiosForm<{
    email: string;
}>({
    email: '',
});

const url = store.url({ vault: props.vaultId });

const handleSubmit = () => {
    form.send<{ data: VaultCollaborator }>({
        url: url,
        method: 'post',
        onError: error => {
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: payload => {
            form.reset();
            activeTab.value = 'users';
            vaultStore.addCollaborator(payload.data);
        },
    });
};

const deleteCollaborator = (userId: number) => {
    openModal(AxiosFormConfirmationModal, {
        title: 'Delete collaborator',
        url: destroy.url({
            vault: props.vaultId,
            user: userId,
        }),
        method: 'delete',
        content: 'Are you sure you want to delete this collaborator?',
        successMessage: 'Collaborator deleted',
        onSuccess: () => {
            vaultStore.removeCollaborator(userId);
        },
    });
};
</script>

<template>
    <Tabs v-model="activeTab">
        <div class="flex gap-2 overflow-x-auto" role="tablist" aria-label="tab options">
            <Tab name="users">Users</Tab>
            <Tab name="inviteUser">Invite user</Tab>
        </div>
        <TabPanel name="users">
            <div class="py-4">
                <table class="w-full table-auto">
                    <tbody>
                        <tr
                            class="border-light-base-300 dark:border-base-500 w-full border-b pt-3 pb-4 last:border-b-0"
                        >
                            <td>
                                <div class="flex items-center">
                                    <p class="mb-1" :title="vaultStore.user?.email">
                                        {{ vaultStore.user?.name }}
                                    </p>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <p class="mb-1">Owner</p>
                                </div>
                            </td>
                            <td class="text-right">
                                <button>
                                    <Trash class="h-4 w-4 opacity-50" />
                                </button>
                            </td>
                        </tr>
                        <tr
                            v-for="collaborator in vaultStore.collaborators"
                            :key="collaborator.id"
                            class="border-light-base-300 dark:border-base-500 w-full border-b pt-3 pb-4 last:border-b-0"
                        >
                            <td>
                                <div class="flex items-center">
                                    <p class="mb-1" :title="collaborator.email">
                                        {{ collaborator.name }}
                                    </p>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <p class="mb-1">
                                        {{ collaborator.accepted ? 'Accepted' : 'Pending' }}
                                    </p>
                                </div>
                            </td>
                            <td class="text-right">
                                <button title="Delete" @click="deleteCollaborator(collaborator.id)">
                                    <Trash class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </TabPanel>
        <TabPanel name="inviteUser">
            <div class="pt-4">
                <form
                    class="flex flex-col gap-6 inert:pointer-events-none"
                    autocomplete="off"
                    novalidate
                    :inert="form.processing"
                    @submit.prevent="handleSubmit"
                >
                    <ModelInput
                        v-model="form.email"
                        name="email"
                        type="text"
                        placeholder="Email"
                        :error="form.errors.email"
                        required
                        autofocus
                    />
                    <div class="flex justify-end gap-2 py-1">
                        <Submit label="Invite" :processing="form.processing" />
                    </div>
                </form>
            </div>
        </TabPanel>
    </Tabs>
</template>
