import { computed, ref, watchEffect } from 'vue';

type Theme = 'light' | 'dark';

const theme = ref<Theme>('light');

const preferredDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

const savedTheme = localStorage.getItem('theme') as Theme | null;
theme.value = savedTheme ?? (preferredDark ? 'dark' : 'light');

watchEffect(() => {
    if (theme.value === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    localStorage.setItem('theme', theme.value);
});

export function useTheme() {
    const isDark = computed(() => theme.value === 'dark');

    const toggleTheme = () => {
        theme.value = theme.value === 'light' ? 'dark' : 'light';
    };

    return { theme, isDark, toggleTheme };
}
