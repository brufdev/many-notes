import { readonly, ref } from 'vue';

const isShowingUnsupportedFiles = ref(localStorage.getItem('isShowingUnsupportedFiles') === 'true');

export function useVaultTreePreferences() {
    function toggleShowingUnsupportedFiles(): void {
        isShowingUnsupportedFiles.value = !isShowingUnsupportedFiles.value;
        localStorage.setItem('isShowingUnsupportedFiles', String(isShowingUnsupportedFiles.value));
    }

    return {
        isShowingUnsupportedFiles: readonly(isShowingUnsupportedFiles),
        toggleShowingUnsupportedFiles,
    };
}
