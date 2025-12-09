import { useLoadingStore } from '@/stores/loading';
import { router } from '@inertiajs/vue3';

interface ReloadWithLoadingOptions {
    only?: string[];
}

export function reloadWithLoading(options: ReloadWithLoadingOptions = {}) {
    const loadingStore = useLoadingStore();

    router.reload({
        ...options,
        onStart: () => loadingStore.setAppLoading(true),
        onFinish: () => loadingStore.setAppLoading(false),
    });
}
