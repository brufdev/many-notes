import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLoadingStore = defineStore('loading', () => {
    const appLoading = ref<boolean>(false);

    function setAppLoading(value: boolean) {
        appLoading.value = value;
    }

    return { appLoading, setAppLoading };
});
