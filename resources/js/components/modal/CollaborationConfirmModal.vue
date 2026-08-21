<script setup lang="ts">
import VaultCollaborationAcceptController from '@/actions/App/Http/Controllers/VaultCollaborationAcceptController';
import VaultCollaborationDeclineController from '@/actions/App/Http/Controllers/VaultCollaborationDeclineController';
import SecondarySubmit from '@/components/form/SecondarySubmit.vue';
import Submit from '@/components/form/Submit.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useRequest } from '@/composables/useRequest';
import { useToast } from '@/composables/useToast';
import { useNotificationStore } from '@/stores/notification';
import { AppNotification } from '@/types';

const props = defineProps<{
    notification: AppNotification;
}>();

const { removeNotification } = useNotificationStore();
const { closeModal } = useModalManager();
const { createToast } = useToast();

const acceptForm = useRequest({});
const declineForm = useRequest({});

const vaultId = Number(props.notification.data.vault_id);

const handleAcceptSubmit = () => {
    acceptForm.post(VaultCollaborationAcceptController.url({ vault: vaultId }), {
        onFailure: () => closeModal(),
        onSuccess: () => {
            removeNotification(props.notification.id);
            closeModal();
            createToast('Collaboration accepted', 'success');
        },
    });
};

const handleDeclineSubmit = () => {
    declineForm.post(VaultCollaborationDeclineController.url({ vault: vaultId }), {
        onFailure: () => closeModal(),
        onSuccess: () => {
            removeNotification(props.notification.id);
            closeModal();
            createToast('Collaboration declined', 'success');
        },
    });
};
</script>

<template>
    <div
        class="flex flex-col gap-6 inert:pointer-events-none"
        :inert="acceptForm.processing || declineForm.processing"
    >
        <p>
            {{ `${notification.data.user_name} has invited you to join the vault` }}
            <span class="font-semibold">{{ notification.data.vault_name }}</span>
        </p>
        <div class="flex justify-end gap-2 py-1">
            <form
                class="flex flex-col gap-6 inert:pointer-events-none"
                @submit.prevent="handleDeclineSubmit"
            >
                <SecondarySubmit label="Decline" :processing="declineForm.processing" />
            </form>

            <form
                class="flex flex-col gap-6 inert:pointer-events-none"
                @submit.prevent="handleAcceptSubmit"
            >
                <Submit label="Accept" autofocus :processing="acceptForm.processing" />
            </form>
        </div>
    </div>
</template>
