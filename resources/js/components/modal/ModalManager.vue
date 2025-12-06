<script lang="ts" setup>
import BaseButton from '@/components/ui/BaseButton.vue';
import { useModalManager } from '@/composables/useModalManager';
import XMark from '@/icons/XMark.vue';
import { computed, nextTick, ref, watch } from 'vue';

const modalManager = useModalManager();
const activeModal = computed(() => modalManager.activeModal.value);
const modalContainer = ref<HTMLElement | null>(null);

function trapFocus(event: KeyboardEvent) {
    const modalElement = modalContainer.value;

    if (!modalElement) {
        return;
    }

    const focusableSelectors = [
        'a[href]',
        'button:not([disabled])',
        'textarea:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
    ];
    const focusables = Array.from(
        modalElement.querySelectorAll<HTMLElement>(focusableSelectors.join(','))
    ).filter(element => !element.hasAttribute('disabled') && element.offsetParent !== null);

    if (focusables.length === 0) {
        event.preventDefault();
        modalElement.focus();

        return;
    }

    const first = focusables[0];
    const last = focusables[focusables.length - 1];
    const active = document.activeElement;

    if (event.shiftKey) {
        if (active === first || active === modalElement) {
            event.preventDefault();
            last.focus();
        }
    } else if (active === last || first === last) {
        event.preventDefault();
        first.focus();
    }
}

function handleKeyDown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        modalManager.closeModal();
    } else if (event.key === 'Tab') {
        trapFocus(event);
    }
}

async function handleFocus() {
    await nextTick();

    const modalElement = modalContainer.value;

    if (!modalElement) {
        return;
    }

    const autofocusElement = modalElement.querySelector<HTMLElement>('[autofocus]');

    if (autofocusElement) {
        autofocusElement.focus();
    } else {
        modalElement.focus();
    }
}

watch(activeModal, async modal => {
    if (modal) {
        document.addEventListener('keydown', handleKeyDown);
        await handleFocus();
    } else {
        document.removeEventListener('keydown', handleKeyDown);
    }
});
</script>

<template>
    <teleport to="body">
        <transition name="fade-scale">
            <div v-if="activeModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="bg-base-950 absolute inset-0 opacity-50"></div>

                <div
                    :class="[
                        'fixed inset-0 z-50 flex justify-center sm:py-5',
                        activeModal.props?.top ? 'items-start' : 'items-end sm:items-center',
                    ]"
                    @click.self="modalManager.closeModal"
                >
                    <div
                        ref="modalContainer"
                        class="bg-light-base-50 dark:bg-base-900 text-light-base-950 dark:text-base-50 relative z-10 flex max-h-full w-full max-w-2xl flex-col gap-6 rounded-lg py-6 shadow-xl"
                        tabindex="-1"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div class="flex justify-between px-6">
                            <h3 class="text-lg">
                                {{ activeModal.props?.title }}
                            </h3>
                            <BaseButton
                                class="px-1"
                                aria-label="Close"
                                @click="modalManager.closeModal"
                            >
                                <XMark class="h-5 w-5" aria-hidden="true" />
                            </BaseButton>
                        </div>
                        <div class="overflow-y-auto px-6">
                            <component
                                :is="activeModal.component"
                                v-bind="activeModal.props"
                                @close="modalManager.closeModal"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<style scoped>
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: opacity 0.3s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
    opacity: 0;
}

.fade-scale-enter-active > div > div,
.fade-scale-leave-active > div > div {
    transition: transform 0.2s ease;
}

.fade-scale-enter-from > div > div,
.fade-scale-leave-to > div > div {
    transform: scale(0.95);
}
</style>
