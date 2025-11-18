import { AppSettings } from '@/types/shared-props';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAppSettingsStore = defineStore('appSettings', () => {
    const localAuthEnabled = ref<boolean | null>(null);

    function setAppSettings(settings: AppSettings | null) {
        if (!settings) {
            localAuthEnabled.value = null;

            return;
        }

        localAuthEnabled.value = settings.local_auth_enabled;
    }

    return { localAuthEnabled, setAppSettings };
});
