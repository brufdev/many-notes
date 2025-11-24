<script setup lang="ts">
import SettingController from '@/actions/App/Http/Controllers/SettingController';
import CheckboxToggle from '@/components/form/CheckboxToggle.vue';
import Submit from '@/components/form/Submit.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { useSettingStore } from '@/stores/setting';
import { AppPageProps } from '@/types';
import type { Page } from '@inertiajs/core';
import { Form } from '@inertiajs/vue3';

const { closeModal } = useModalManager();
const { createToast } = useToast();
const settingStore = useSettingStore();

const handleSuccess = (page: Page<AppPageProps>) => {
    settingStore.setSettings(page.props.app?.settings ?? null);
    closeModal();
    createToast('Settings updated', 'success');
};
</script>

<template>
    <div>
        <Form
            v-slot="{ processing }"
            v-bind="SettingController.update.form()"
            class="flex flex-col gap-6"
            autocomplete="off"
            novalidate
            @success="handleSuccess"
        >
            <CheckboxToggle
                name="registration"
                :value="settingStore.registration ?? false"
                label="Registration"
            />
            <CheckboxToggle
                name="auto_update_check"
                :value="settingStore.auto_update_check ?? false"
                label="Automatic update check"
            />
            <Submit label="Save" :processing="processing" />
        </Form>
    </div>
</template>
