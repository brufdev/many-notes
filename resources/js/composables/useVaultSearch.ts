import type { Ref } from 'vue';
import { ref, watch } from 'vue';

function isFileVisible(containerHeight: number, elementTop: number, elementBottom: number) {
    return (
        elementTop >= 0 &&
        elementTop <= containerHeight &&
        elementBottom >= 0 &&
        elementBottom <= containerHeight
    );
}

export function useVaultSearch(onSearch: (search: string) => void, fileCount: Ref<number>) {
    const search = ref('');
    const hasSearched = ref(false);
    const selectedFile = ref(0);
    const listRef = ref<HTMLUListElement | null>(null);
    const scrollContainerRef = ref<HTMLElement | null>(null);

    let debounceTimer: ReturnType<typeof setTimeout> | null = null;

    watch(search, value => {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        debounceTimer = setTimeout(() => {
            selectedFile.value = 0;
            hasSearched.value = true;
            onSearch(value);
        }, 500);
    });

    function selectFile(index: number) {
        if (index < 0 || index > fileCount.value - 1) {
            return;
        }

        selectedFile.value = index;
    }

    function selectPreviousFile() {
        selectFile(selectedFile.value === 0 ? fileCount.value - 1 : selectedFile.value - 1);
        ensureFileIsVisible();
    }

    function selectNextFile() {
        selectFile(selectedFile.value === fileCount.value - 1 ? 0 : selectedFile.value + 1);
        ensureFileIsVisible();
    }

    function ensureFileIsVisible() {
        const container = scrollContainerRef.value;

        if (!container || !listRef.value || fileCount.value === 0) {
            return;
        }

        const scrollContainer = container.parentElement;
        const items = listRef.value.getElementsByTagName('div');
        const fileElement = items[selectedFile.value];

        if (!scrollContainer || !fileElement) {
            return;
        }

        if (selectedFile.value === 0) {
            scrollContainer.scroll({ top: 0, behavior: 'smooth' });

            return;
        }

        if (selectedFile.value === fileCount.value - 1) {
            const top = scrollContainer.scrollHeight - scrollContainer.clientHeight;
            scrollContainer.scroll({ top: top, behavior: 'smooth' });

            return;
        }

        const containerTop = scrollContainer.getBoundingClientRect().top;
        const fileRect = fileElement.getBoundingClientRect();
        const elementTop = fileRect.top - containerTop;
        const elementBottom = fileRect.bottom - containerTop;

        if (isFileVisible(scrollContainer.clientHeight, elementTop, elementBottom)) {
            return;
        }

        if (elementTop < 0) {
            const top = scrollContainer.scrollTop + elementTop;
            scrollContainer.scroll({ top: top, behavior: 'smooth' });
        } else {
            const top = scrollContainer.scrollTop + elementBottom - scrollContainer.clientHeight;
            scrollContainer.scroll({ top: top, behavior: 'smooth' });
        }
    }

    return {
        search,
        hasSearched,
        selectedFile,
        listRef,
        scrollContainerRef,
        selectFile,
        selectPreviousFile,
        selectNextFile,
    };
}
