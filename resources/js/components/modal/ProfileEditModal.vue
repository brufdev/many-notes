<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/ProfileController';
import Input from '@/components/form/Input.vue';
import Submit from '@/components/form/Submit.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { useAppSettingsStore } from '@/stores/appSettings';
import { useUserStore } from '@/stores/user';
import { SharedProps } from '@/types/shared-props';
import type { Page } from '@inertiajs/core';
import { Form } from '@inertiajs/vue3';

const { closeModal } = useModalManager();
const { createToast } = useToast();
const appSettings = useAppSettingsStore();
const userStore = useUserStore();

const handleSuccess = (page: Page<SharedProps>) => {
    userStore.setUser(page.props.auth?.user ?? null);
    closeModal();
    createToast('Profile updated', 'success');
};
</script>

<template>
    <div>
        <Form
            v-slot="{ errors, processing }"
            v-bind="ProfileController.update.form()"
            class="flex flex-col gap-6"
            autocomplete="off"
            novalidate
            @success="handleSuccess"
        >
            <Input
                name="name"
                type="text"
                :value="userStore.name"
                placeholder="Name"
                :error="errors.name"
                :disabled="!appSettings.localAuthEnabled"
                required
                autofocus
            />
            <Input
                name="email"
                type="email"
                :value="userStore.email"
                placeholder="Email"
                :error="errors.email"
                :disabled="!appSettings.localAuthEnabled"
                required
            />
            <Submit v-if="appSettings.localAuthEnabled" label="Edit" :processing="processing" />
        </Form>
    </div>
</template>
