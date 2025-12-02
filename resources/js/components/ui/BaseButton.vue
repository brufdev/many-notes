<script setup lang="ts">
interface Props {
    type?: 'button' | 'reset' | 'submit';
}

const props = withDefaults(defineProps<Props>(), {
    type: 'button',
});
</script>

<template>
    <button
        class="focus-visible:ring-light-base-600 dark:focus-visible:ring-base-400 rounded focus:outline-none focus-visible:ring-1"
        :type="props.type"
    >
        <slot />
    </button>
</template>
