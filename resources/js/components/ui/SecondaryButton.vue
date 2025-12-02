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
        class="border-light-base-400 dark:border-base-700 bg-light-base-300 dark:bg-base-500 hover:bg-light-base-400 dark:hover:bg-base-700 text-light-base-950 dark:text-base-50 focus-visible:outline-light-base-600 dark:focus-visible:outline-base-400 items-centerXXX gap-2XXX relative flex justify-center rounded-md border px-4 py-1.5 text-sm leading-6 font-semibold shadow-sm focus-visible:outline focus-visible:outline-1 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-75"
        :type="props.type"
    >
        <slot />
    </button>
</template>
