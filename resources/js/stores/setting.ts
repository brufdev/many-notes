import { Settings } from '@/types/settings';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useSettingStore = defineStore('setting', () => {
    const local_auth_enabled = ref<boolean | null>(null);
    const registration = ref<boolean | null>(null);
    const auto_update_check = ref<boolean | null>(null);

    function setSettings(settings: Settings | null) {
        local_auth_enabled.value = settings?.local_auth_enabled ?? null;
        registration.value = settings?.registration ?? null;
        auto_update_check.value = settings?.auto_update_check ?? null;
    }

    return { local_auth_enabled, registration, auto_update_check, setSettings };
});
