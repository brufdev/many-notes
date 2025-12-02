<script setup lang="ts">
import PrimaryButton from '@/components/ui/PrimaryButton.vue';
import Spinner from '@/icons/Spinner.vue';

defineProps<{
    label: string;
    fullWidth?: boolean;
    processing: boolean;
}>();
</script>

<template>
    <PrimaryButton type="submit">
        <span :class="!fullWidth && processing ? 'opacity-0' : ''">{{ label }}</span>

        <span
            v-if="processing"
            class="absolute top-0 bottom-0 flex items-center"
            :class="fullWidth ? 'right-4' : ''"
        >
            <Spinner class="h-5 w-5 animate-spin" />
        </span>
    </PrimaryButton>
</template>
