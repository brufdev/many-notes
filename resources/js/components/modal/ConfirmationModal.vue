<script setup lang="ts">
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { RouteFormDefinition } from '@/wayfinder';
import { Form, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { closeModal } = useModalManager();
const { createToast } = useToast();

const props = defineProps<{
    routeForm: RouteFormDefinition<'post'>;
    content: string;
    successMessage: string;
}>();

const errors = computed(() => usePage().props.errors);

const handleError = () => {
    closeModal();
    createToast(errors.value.delete, 'error');
};

const handleSuccess = () => {
    closeModal();
    createToast(props.successMessage, 'success');
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
            @error="handleError"
            @success="handleSuccess"
        >
            <p>{{ content }}</p>
            <div class="flex justify-end gap-2 pb-1">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <Submit label="Confirm" autofocus :processing="processing" />
            </div>
        </Form>
    </div>
</template>
