import type { Setting } from '@/types';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useSettingStore = defineStore('setting', () => {
    const localAuthEnabled = ref<boolean | null>(null);

    function setSetting(setting: Setting | null) {
        if (!setting) {
            localAuthEnabled.value = null;

            return;
        }

        localAuthEnabled.value = setting.local_auth_enabled;
    }

    return { localAuthEnabled, setSetting };
});
