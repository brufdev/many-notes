import { onMounted, onUnmounted, ref } from 'vue';

export function useScreenSize() {
    const mediaQuery = globalThis.matchMedia('(min-width: 1024px)');

    const isSmallScreen = ref(!mediaQuery.matches);

    function handleChange(e: MediaQueryListEvent) {
        isSmallScreen.value = !e.matches;
    }

    onMounted(() => {
        isSmallScreen.value = !mediaQuery.matches;

        mediaQuery.addEventListener('change', handleChange);
    });

    onUnmounted(() => {
        mediaQuery.removeEventListener('change', handleChange);
    });

    return {
        isSmallScreen,
    };
}
