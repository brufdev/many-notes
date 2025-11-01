<script setup lang="ts">
defineProps<{
    name: string;
    type: string;
    label?: string;
    error?: string;
}>();
</script>

<template>
    <label class="flex flex-col gap-2 text-base font-medium">
        <span v-if="label">
            {{ label }}
            <span v-if="'required' in $attrs" class="text-error-500 opacity-75" aria-hidden="true">
                *
            </span>
        </span>
        <input
            v-bind="$attrs"
            :name="name"
            :type="type"
            :class="[
                'bg-light-base-100 dark:bg-base-800 text-light-base-700 dark:text-base-200 block w-full rounded-lg border px-2 py-1.5 focus:ring-0 focus:outline focus:outline-0',
                error
                    ? 'border-error-500 focus:border-error-700 dark:border-error-500 dark:focus:border-error-700'
                    : 'border-light-base-300 dark:border-base-500 focus:border-light-base-600 dark:focus:border-base-400',
            ]"
            required
            autofocus
        />

        <p v-if="error" class="text-error-500 text-sm" aria-live="assertive">
            {{ error }}
        </p>
    </label>
</template>
