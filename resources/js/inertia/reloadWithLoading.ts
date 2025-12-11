import { useLayoutStore } from '@/stores/layout';
import { router } from '@inertiajs/vue3';

interface ReloadWithLoadingOptions {
    only?: string[];
}

export function reloadWithLoading(options: ReloadWithLoadingOptions = {}) {
    const layoutStore = useLayoutStore();

    router.reload({
        ...options,
        onStart: () => layoutStore.setAppLoading(true),
        onFinish: () => layoutStore.setAppLoading(false),
    });
}
