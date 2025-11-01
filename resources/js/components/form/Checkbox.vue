<script setup lang="ts">
defineProps<{
    name: string;
    label?: string;
    error?: string;
}>();
</script>

<template>
    <label class="inline-flex items-center text-base font-medium">
        <input
            v-bind="$attrs"
            :name="name"
            type="checkbox"
            :class="[
                'bg-light-base-100 dark:bg-base-800 checked:bg-primary-400 dark:checked:bg-primary-500 border-light-base-300 dark:border-base-500 focus-visible:outline-light-base-600 dark:focus-visible:outline-base-400 rounded shadow-sm focus:ring-0 focus:ring-offset-0 focus-visible:outline focus-visible:outline-1 focus-visible:outline-offset-2',
                error
                    ? 'border-error-500 focus:border-error-700 dark:border-error-500 dark:focus:border-error-700 border'
                    : '',
            ]"
        />

        <span v-if="label" class="ms-2 text-sm text-gray-600 dark:text-gray-400">
            {{ label }}
            <span v-if="'required' in $attrs" class="text-error-500 opacity-75" aria-hidden="true">
                *
            </span>
        </span>

        <p v-if="error" class="text-error-500 text-sm" aria-live="assertive">
            {{ error }}
        </p>
    </label>
</template>
