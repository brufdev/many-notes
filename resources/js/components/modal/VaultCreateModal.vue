<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/VaultController';
import { ModelInput, Submit } from '@/components/form';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useModalManager, useRequest, useToast } from '@/composables';
import { show } from '@/routes/vaults';
import { useLayoutStore } from '@/stores/layout';
import { VaultCreated } from '@/types/vault';
import { router } from '@inertiajs/vue3';

const { closeModal } = useModalManager();
const { createToast } = useToast();
const layoutStore = useLayoutStore();

const form = useRequest<{ name: string }>({ name: '' });

const url = store.url();

const handleSubmit = () => {
    form.post(url, {
        onFailure: () => closeModal(),
        onSuccess: (response: { data: VaultCreated }) => {
            closeModal();

            router.visit(show.url({ vault: response.data.id }), {
                onStart: () => layoutStore.setAppLoading(true),
                onSuccess: () => createToast('Vault created', 'success'),
                onFinish: () => layoutStore.setAppLoading(false),
            });
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
