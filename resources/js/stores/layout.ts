import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLayoutStore = defineStore('layout', () => {
    const isAppLoading = ref<boolean>(false);
    const isTreeViewLoading = ref<boolean>(false);
    const isVaultNodeUpdating = ref<boolean>(false);
    const showToggleContentWidthButton = ref<boolean>(false);
    const isContentWidthFull = ref<boolean>(
        localStorage.getItem('contentWidthFull') === 'true' || false
    );

    function setAppLoading(value: boolean) {
        isAppLoading.value = value;
    }

    function setTreeViewLoading(value: boolean) {
        isTreeViewLoading.value = value;
    }

    function setVaultNodeUpdating(value: boolean) {
        isVaultNodeUpdating.value = value;
    }

    function setShowToggleContentWidthButton(value: boolean) {
        showToggleContentWidthButton.value = value;
    }

    function toggleContentWidth() {
        isContentWidthFull.value = !isContentWidthFull.value;
        localStorage.setItem('contentWidthFull', isContentWidthFull.value.toString());
    }

    return {
        isAppLoading,
        isTreeViewLoading,
        isVaultNodeUpdating,
        isContentWidthFull,
        showToggleContentWidthButton,
        setAppLoading,
        setTreeViewLoading,
        setVaultNodeUpdating,
        setShowToggleContentWidthButton,
        toggleContentWidth,
    };
});
