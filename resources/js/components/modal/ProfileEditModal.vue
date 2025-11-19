<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/ProfileController';
import Input from '@/components/form/Input.vue';
import Submit from '@/components/form/Submit.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { useSettingStore } from '@/stores/setting';
import { useUserStore } from '@/stores/user';
import { AppPageProps } from '@/types';
import type { Page } from '@inertiajs/core';
import { Form } from '@inertiajs/vue3';

const { closeModal } = useModalManager();
const { createToast } = useToast();
const settingStore = useSettingStore();
const userStore = useUserStore();

const handleSuccess = (page: Page<AppPageProps>) => {
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
                :disabled="!settingStore.localAuthEnabled"
                required
                autofocus
            />
            <Input
                name="email"
                type="email"
                :value="userStore.email"
                placeholder="Email"
                :error="errors.email"
                :disabled="!settingStore.localAuthEnabled"
                required
            />
            <Submit v-if="settingStore.localAuthEnabled" label="Edit" :processing="processing" />
        </Form>
    </div>
</template>
