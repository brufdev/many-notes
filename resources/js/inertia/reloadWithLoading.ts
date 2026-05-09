import { useLayoutStore } from '@/stores/layout';
import { router } from '@inertiajs/vue3';

export interface ReloadWithLoadingOptions {
    only?: string[];
    onSuccess?: () => void;
}

export function reloadWithLoading(options: ReloadWithLoadingOptions = {}) {
    const layoutStore = useLayoutStore();

    router.reload({
        ...options,
        onStart: () => layoutStore.setAppLoading(true),
        onFinish: () => layoutStore.setAppLoading(false),
    });
}
