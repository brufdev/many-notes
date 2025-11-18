import { useAppSettingsStore } from '@/stores/appSettings';
import { useUserStore } from '@/stores/user';
import { SharedProps } from '@/types/shared-props';

export function hydrateStoresFromPageProps(props: SharedProps) {
    const userStore = useUserStore();
    userStore.setUser(props.auth?.user ?? null);

    const appSettingsStore = useAppSettingsStore();
    appSettingsStore.setAppSettings(props.app?.settings ?? null);
}
