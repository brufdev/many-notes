<script setup lang="ts">
import { update } from '@/actions/App/Http/Controllers/SettingController';
import CheckboxToggle from '@/components/form/CheckboxToggle.vue';
import Submit from '@/components/form/Submit.vue';
import SecondaryButton from '@/components/ui/SecondaryButton.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { useSettingStore } from '@/stores/setting';
import { AdminEditableSettings } from '@/types/settings';

const { closeModal } = useModalManager();
const { createToast } = useToast();
const settingStore = useSettingStore();

const form = useAxiosForm<AdminEditableSettings>({
    registration: settingStore.registration ?? false,
    auto_update_check: settingStore.auto_update_check ?? false,
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
        onSuccess: (response: { settings: AdminEditableSettings }) => {
            closeModal();
            createToast('Settings updated', 'success');
            settingStore.setSettings(response.settings);
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
        <CheckboxToggle v-model="form.registration" name="registration" label="Registration" />
        <CheckboxToggle
            v-model="form.auto_update_check"
            name="auto_update_check"
            label="Automatic update check"
        />
        <div class="flex justify-end gap-2 py-1">
            <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
            <Submit label="Save" :processing="form.processing" />
        </div>
    </form>
</template>
