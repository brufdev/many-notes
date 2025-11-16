import { Component, markRaw, shallowRef } from 'vue';

interface ModalEntry {
    component: Component;
    props?: Record<string, unknown>;
}

const activeModal = shallowRef<ModalEntry | null>(null);

export function useModalManager() {
    function openModal(component: Component, props?: Record<string, unknown>) {
        activeModal.value = {
            component: markRaw(component),
            props,
        };
    }

    function closeModal() {
        activeModal.value = null;
    }

    return { activeModal, openModal, closeModal };
}
