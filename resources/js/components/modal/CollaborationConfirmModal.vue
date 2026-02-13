<script setup lang="ts">
import VaultCollaborationAcceptController from '@/actions/App/Http/Controllers/VaultCollaborationAcceptController';
import VaultCollaborationDeclineController from '@/actions/App/Http/Controllers/VaultCollaborationDeclineController';
import SecondarySubmit from '@/components/form/SecondarySubmit.vue';
import Submit from '@/components/form/Submit.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { useNotificationStore } from '@/stores/notification';
import { AppNotification } from '@/types';
import { ref } from 'vue';

const { closeModal } = useModalManager();
const { removeNotification } = useNotificationStore();
const { createToast } = useToast();

const props = defineProps<{
    notification: AppNotification;
}>();

const submitting = ref(false);

const acceptForm = useAxiosForm({});
const declineForm = useAxiosForm({});

const handleAcceptSubmit = () => {
    acceptForm.send({
        url: VaultCollaborationAcceptController.url({
            vault: Number(props.notification.data.vault_id),
        }),
        method: 'post',
        onError: error => {
            closeModal();
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: () => {
            removeNotification(props.notification.id);
            closeModal();
            createToast('Collaboration accepted', 'success');
        },
    });
};

const handleDeclineSubmit = () => {
    declineForm.send({
        url: VaultCollaborationDeclineController.url({
            vault: Number(props.notification.data.vault_id),
        }),
        method: 'post',
        onError: error => {
            closeModal();
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: () => {
            removeNotification(props.notification.id);
            closeModal();
            createToast('Collaboration declined', 'success');
        },
    });
};
</script>

<template>
    <div class="flex flex-col gap-6 inert:pointer-events-none" :inert="submitting">
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
