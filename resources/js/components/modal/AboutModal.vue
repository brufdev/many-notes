<script setup lang="ts">
import { AppPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';

const props = usePage().props as AppPageProps;
</script>

<template>
    <div class="flex flex-col gap-4">
        <p>Follow the development, report any issues, and check for new versions on GitHub.</p>

        <div>
            <h2>Version</h2>
            <p>{{ props.metadata?.app_version }}</p>
        </div>

        <div v-if="props.metadata?.update_available">
            <h2>Update available</h2>
            <p class="text-success-600">{{ props.metadata?.latest_version }}</p>
        </div>

        <div>
            <h2>GitHub</h2>
            <p>
                <a
                    :href="props.metadata?.github_url"
                    class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                    target="_blank"
                >
                    {{ props.metadata?.github_url }}
                </a>
            </p>
        </div>
    </div>
</template>
