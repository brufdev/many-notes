import { useLayoutStore } from '@/stores/layout';
import { onMounted, onUnmounted, Ref } from 'vue';

export function useContentWidthPreference(mainSectionRef: Ref<HTMLElement | null>) {
    const layoutStore = useLayoutStore();

    let resizeObserver: ResizeObserver | null = null;

    function handleResize() {
        if (mainSectionRef.value === null) {
            return;
        }

        layoutStore.setShowToggleContentWidthButton(mainSectionRef.value.offsetWidth > 768);
    }

    onMounted(() => {
        if (mainSectionRef.value === null) {
            return;
        }

        handleResize();

        resizeObserver = new ResizeObserver(handleResize);
        resizeObserver.observe(mainSectionRef.value);
    });

    onUnmounted(() => {
        resizeObserver?.disconnect();
    });
}
