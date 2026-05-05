import { reloadWithLoading, type ReloadWithLoadingOptions } from '@/inertia/reloadWithLoading';
import { onMounted } from 'vue';

let pendingHistoryReload = false;

globalThis.addEventListener('popstate', () => {
    pendingHistoryReload = true;
});

export function useReloadOnPopstate(options?: ReloadWithLoadingOptions) {
    onMounted(() => {
        if (pendingHistoryReload) {
            pendingHistoryReload = false;
            reloadWithLoading(options);
        }
    });
}
