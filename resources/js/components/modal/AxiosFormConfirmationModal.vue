<script setup lang="ts">
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { AxiosResponse } from 'axios';

const { closeModal } = useModalManager();
const { createToast } = useToast();

const props = defineProps<{
    url: string;
    method: 'delete';
    content: string;
    successMessage: string;
    onSuccess: (response: AxiosResponse) => void;
}>();

const form = useAxiosForm({});

const handleSubmit = () => {
    form.send({
        url: props.url,
        method: props.method,
        onError: error => {
            closeModal();
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: (response: AxiosResponse) => {
            closeModal();
            createToast(props.successMessage, 'success');
            props.onSuccess?.(response);
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
        <p>{{ content }}</p>
        <div class="flex justify-end gap-2 py-1">
            <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
            <Submit label="Confirm" autofocus :processing="form.processing" />
        </div>
    </form>
</template>
