import { useSettingStore } from '@/stores/setting';
import { useUserStore } from '@/stores/user';
import { AppPageProps } from '@/types';

export function hydrateStoresFromPageProps(props: AppPageProps) {
    const userStore = useUserStore();
    userStore.setUser(props.auth?.user ?? null);

    const settingStore = useSettingStore();
    settingStore.setSetting(props.app?.setting ?? null);
}
