import { Component, computed, markRaw, ref } from 'vue';

interface ModalEntry {
    component: Component;
    props?: Record<string, unknown>;
}

const modalStack = ref<ModalEntry[]>([]);

export function useModalManager() {
    function openModal(component: Component, props?: Record<string, unknown>) {
        modalStack.value.push({
            component: markRaw(component),
            props,
        });
    }

    function closeModal() {
        modalStack.value.pop();
    }

    const activeModal = computed(() => {
        return modalStack.value.at(-1) ?? null;
    });

    return { modalStack, activeModal, openModal, closeModal };
}
