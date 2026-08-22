import { useNotificationStore } from '@/stores/notification';
import { useSettingStore } from '@/stores/setting';
import { useUserStore } from '@/stores/user';
import { AppPageProps } from '@/types';

export function hydrateStoresFromPageProps(props: AppPageProps) {
    const app = props.app;

    if (app?.user !== undefined) {
        useUserStore().setUser(app.user);
    }

    if (app?.settings !== undefined) {
        useSettingStore().setSettings(app.settings);
    }

    if (app?.notifications !== undefined) {
        useNotificationStore().setNotifications(app.notifications);
    }
}
