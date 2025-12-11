import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLayoutStore = defineStore('layout', () => {
    const appLoading = ref<boolean>(false);

    function setAppLoading(value: boolean) {
        appLoading.value = value;
    }

    return { appLoading, setAppLoading };
});
