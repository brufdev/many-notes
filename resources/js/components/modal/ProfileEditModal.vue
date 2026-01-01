<script setup lang="ts">
import { update } from '@/actions/App/Http/Controllers/ProfileController';
import ModelInput from '@/components/form/ModelInput.vue';
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { useSettingStore } from '@/stores/setting';
import { useUserStore } from '@/stores/user';
import { User } from '@/types';

const { closeModal } = useModalManager();
const { createToast } = useToast();
const settingStore = useSettingStore();
const userStore = useUserStore();

const form = useAxiosForm<{ name: string; email: string }>({
    name: userStore.name ?? '',
    email: userStore.email ?? '',
});

const handleSubmit = () => {
    form.send({
        url: update.url(),
        method: 'patch',
        onError: error => {
            closeModal();
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: (response: { user: User }) => {
            closeModal();
            createToast('Profile updated', 'success');
            userStore.setUser(response.user);
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
            :disabled="!settingStore.local_auth_enabled"
            required
            autofocus
        />
        <ModelInput
            v-model="form.email"
            name="email"
            type="email"
            placeholder="Email"
            :error="form.errors.email"
            :disabled="!settingStore.local_auth_enabled"
            required
        />
        <div v-if="settingStore.local_auth_enabled" class="flex justify-end gap-2 py-1">
            <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
            <Submit label="Save" :processing="form.processing" />
        </div>
    </form>
</template>
