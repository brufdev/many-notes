<script setup lang="ts">
import SecondarySubmit from '@/components/form/SecondarySubmit.vue';
import Submit from '@/components/form/Submit.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { AppNotification } from '@/types';
import { ref } from 'vue';

const props = defineProps<{
    notification: AppNotification;
}>();

const submitting = ref(false);

const declineForm = useAxiosForm({});
const acceptForm = useAxiosForm({});

const handleDeclineSubmit = () => {
};

const handleAcceptSubmit = () => {
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
