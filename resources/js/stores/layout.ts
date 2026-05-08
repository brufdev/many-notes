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

    const isLeftPanelOpen = ref<boolean>(false);
    const isRightPanelOpen = ref<boolean>(false);

    const leftPanelPreferredOpen = ref<boolean>(
        localStorage.getItem('leftPanelPreferredOpen')
            ? localStorage.getItem('leftPanelPreferredOpen') === 'true'
            : true
    );
    const rightPanelPreferredOpen = ref<boolean>(
        localStorage.getItem('rightPanelPreferredOpen')
            ? localStorage.getItem('rightPanelPreferredOpen') === 'true'
            : true
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

    function toggleLeftPanel(isSmallScreen: boolean) {
        if (!isSmallScreen) {
            leftPanelPreferredOpen.value = !leftPanelPreferredOpen.value;
            localStorage.setItem('leftPanelPreferredOpen', leftPanelPreferredOpen.value.toString());
        }

        isLeftPanelOpen.value = !isLeftPanelOpen.value;
    }

    function toggleRightPanel(isSmallScreen: boolean) {
        if (!isSmallScreen) {
            rightPanelPreferredOpen.value = !rightPanelPreferredOpen.value;
            localStorage.setItem(
                'rightPanelPreferredOpen',
                rightPanelPreferredOpen.value.toString()
            );
        }

        isRightPanelOpen.value = !isRightPanelOpen.value;
    }

    function closePanels() {
        isLeftPanelOpen.value = false;
        isRightPanelOpen.value = false;
    }

    function syncPanelsWithScreen(isSmallScreen: boolean) {
        if (isSmallScreen) {
            closePanels();
        } else {
            isLeftPanelOpen.value = leftPanelPreferredOpen.value;
            isRightPanelOpen.value = rightPanelPreferredOpen.value;
        }
    }

    return {
        isAppLoading,
        isTreeViewLoading,
        isVaultNodeUpdating,
        isContentWidthFull,
        showToggleContentWidthButton,
        isLeftPanelOpen,
        isRightPanelOpen,
        setAppLoading,
        setTreeViewLoading,
        setVaultNodeUpdating,
        setShowToggleContentWidthButton,
        toggleContentWidth,
        toggleLeftPanel,
        toggleRightPanel,
        closePanels,
        syncPanelsWithScreen,
    };
});
