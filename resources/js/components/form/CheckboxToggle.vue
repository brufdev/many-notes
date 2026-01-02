<script setup lang="ts">
defineProps<{
    name: string;
    label: string;
}>();

const model = defineModel<boolean>({
    default: false,
});
</script>

<template>
    <label class="inline-flex cursor-pointer justify-between">
        <span class="me-3 text-sm font-medium">{{ label }}</span>
        <input v-bind="$attrs" v-model="model" :name="name" type="checkbox" class="peer sr-only" />
        <div
            class="bg-light-base-600 dark:bg-base-500 peer after:bg-light-base-100 peer-checked:bg-primary-300 dark:peer-checked:bg-primary-600 relative h-6 w-11 rounded-full peer-focus:outline-none after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:transition-all after:content-[''] peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full"
        ></div>
    </label>
</template>
