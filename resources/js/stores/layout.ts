import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLayoutStore = defineStore('layout', () => {
    const isAppLoading = ref<boolean>(false);
    const isTreeViewLoading = ref<boolean>(false);

    function setAppLoading(value: boolean) {
        isAppLoading.value = value;
    }

    function setTreeViewLoading(value: boolean) {
        isTreeViewLoading.value = value;
    }

    return {
        isAppLoading,
        isTreeViewLoading,
        setAppLoading,
        setTreeViewLoading,
    };
});
