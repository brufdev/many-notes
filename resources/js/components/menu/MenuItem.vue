<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, type Component } from 'vue';

const props = defineProps<{
    label?: string;
    href?: string;
    method?: string;
    icon?: Component;
    iconClass?: string;
    disabled?: boolean;
}>();

const isLink = computed(() => !!props.href);
</script>

<template>
    <component
        :is="isLink ? Link : 'button'"
        :href="href"
        :method="method"
        class="hover:bg-light-base-400 dark:hover:bg-base-700 text-light-base-950 dark:text-base-50 flex w-full items-center gap-2 rounded px-2 py-1 transition-colors"
        :class="{ 'pointer-events-none opacity-50': disabled }"
        :disabled="disabled"
    >
        <template v-if="label">
            <component :is="icon" v-if="icon" :class="`h-4 w-4 ${iconClass}`"></component>
            <span>{{ label }}</span>
        </template>

        <slot v-else />
    </component>
</template>
