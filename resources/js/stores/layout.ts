import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLayoutStore = defineStore('layout', () => {
    const isAppLoading = ref<boolean>(false);

    function setAppLoading(value: boolean) {
        isAppLoading.value = value;
    }

    return { isAppLoading, setAppLoading };
});
