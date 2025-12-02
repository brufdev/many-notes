<script setup lang="ts">
import Submit from '@/components/form/Submit.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { RouteFormDefinition } from '@/wayfinder';
import { Form } from '@inertiajs/vue3';
import SecondaryButton from '../ui/SecondaryButton.vue';

const { closeModal } = useModalManager();
const { createToast } = useToast();

defineProps<{
    routeForm: RouteFormDefinition<'post'>;
    message: string;
}>();

const handleSuccess = () => {
    closeModal();
    createToast('Vault deleted', 'success');
};
</script>

<template>
    <div>
        <Form
            v-slot="{ processing }"
            v-bind="routeForm"
            class="flex flex-col gap-6 inert:pointer-events-none"
            autocomplete="off"
            novalidate
            disable-while-processing
            @success="handleSuccess"
        >
            <p>{{ message }}</p>
            <div class="flex justify-end gap-2 pb-1">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <Submit label="Confirm" autofocus :processing="processing" />
            </div>
        </Form>
    </div>
</template>
