import { useNotificationStore } from '@/stores/notification';
import { useSettingStore } from '@/stores/setting';
import { useUserStore } from '@/stores/user';
import { AppPageProps } from '@/types';

export function hydrateStoresFromPageProps(props: AppPageProps) {
    const userStore = useUserStore();
    userStore.setUser(props.app?.user ?? null);

    const settingStore = useSettingStore();
    settingStore.setSettings(props.app?.settings ?? null);

    const notificationStore = useNotificationStore();
    notificationStore.setNotifications(props.app?.notifications ?? []);
}
