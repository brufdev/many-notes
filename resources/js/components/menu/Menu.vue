<script setup lang="ts">
import { useMenu } from '@/composables';

type MenuType = 'dropdown' | 'hover';

const props = defineProps<{
    type: MenuType;
    disabled?: boolean;
}>();

const { triggerRef, menuRef, isOpen, position, closeMenu, toggleMenu } = useMenu();

function handleClick() {
    if (props.type === 'dropdown') {
        toggleMenu();
    }
}

function handleHover() {
    if (props.type === 'hover' && !props.disabled) {
        toggleMenu();
    }
}

defineExpose({ closeMenu });
</script>

<template>
    <div
        ref="triggerRef"
        class="relative"
        @click="handleClick"
        @mouseenter="handleHover"
        @mouseleave="handleHover"
    >
        <div :class="['select-none', !disabled && 'cursor-pointer']">
            <slot name="trigger" />

            <Teleport to="body" :disabled="props.type === 'hover'">
                <div
                    v-if="isOpen"
                    ref="menuRef"
                    class="fixed z-[35] p-1.5"
                    :style="{ top: `${position.top}px`, left: `${position.left}px` }"
                >
                    <div
                        class="bg-light-base-200 dark:bg-base-950 border-light-base-300 dark:border-base-500 text-light-base-950 dark:text-base-50 rounded-md border px-1.5 py-2 text-sm shadow-lg"
                    >
                        <slot :close-menu="closeMenu" />
                    </div>
                </div>
            </Teleport>
        </div>
    </div>
</template>
