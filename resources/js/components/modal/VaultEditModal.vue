<script setup lang="ts">
import VaultController from '@/actions/App/Http/Controllers/VaultController';
import Input from '@/components/form/Input.vue';
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { Form, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { closeModal } = useModalManager();
const { createToast } = useToast();

defineProps<{
    id: number;
    name: string;
}>();

const pageErrors = computed(() => usePage().props.errors);

const handleError = () => {
    if (!pageErrors.value.update) {
        return;
    }

    closeModal();
    createToast(pageErrors.value.update, 'error');
};

const handleSuccess = () => {
    closeModal();
    createToast('Vault updated', 'success');
};
</script>

<template>
    <div>
        <Form
            v-slot="{ errors, processing }"
            v-bind="VaultController.update.form({ vault: id })"
            class="flex flex-col gap-6"
            autocomplete="off"
            novalidate
            disable-while-processing
            @error="handleError"
            @success="handleSuccess"
        >
            <Input
                name="name"
                type="text"
                :value="name"
                placeholder="Name"
                :error="errors.name"
                required
                autofocus
            />
            <div class="flex justify-end gap-2 pb-1">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <Submit label="Save" :processing="processing" />
            </div>
        </Form>
    </div>
</template>
