<script setup lang="ts">
import { Submit } from '@/components/form';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useModalManager, useRequest, useToast } from '@/composables';

const props = defineProps<{
    url: string;
    method: 'delete';
    content: string;
    successMessage: string;
    onSuccess?: (response: unknown) => void;
}>();

const { closeModal } = useModalManager();
const { createToast } = useToast();

const form = useRequest({});

const handleSubmit = () => {
    form[props.method](props.url, {
        onFailure: () => closeModal(),
        onInvalid: errors => {
            closeModal();

            const message = Object.values(errors).at(0);

            if (typeof message === 'string') {
                createToast(message, 'error');
            }
        },
        onSuccess: response => {
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
