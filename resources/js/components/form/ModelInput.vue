<script setup lang="ts">
import { computed, useId } from 'vue';

const props = defineProps<{
    name: string;
    type: string;
    label?: string;
    error?: string;
}>();

const model = defineModel<string | number | null>({
    default: null,
});

const hasError = computed(() => !!props.error);
const errorId = `${props.name}-${useId()}-error`;
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
            v-model="model"
            :name="name"
            :type="type"
            :class="[
                'bg-light-base-50 dark:bg-base-900 text-light-base-900 dark:text-base-100 placeholder:text-light-base-600 dark:placeholder:text-base-400 block w-full rounded-lg border px-2 py-1.5 focus:ring-0 focus:outline focus:outline-0',
                error
                    ? 'border-error-500 focus:border-error-700 dark:border-error-500 dark:focus:border-error-700'
                    : 'border-light-base-300 dark:border-base-500 focus:border-light-base-600 dark:focus:border-base-400',
            ]"
            :aria-invalid="hasError"
            :aria-describedby="hasError ? errorId : undefined"
        />

        <p v-if="hasError" :id="errorId" class="text-error-500 text-sm">
            {{ error }}
        </p>
    </label>
</template>
