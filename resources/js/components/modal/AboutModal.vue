<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Avatar from '../ui/Avatar.vue';
import Link from '@/icons/Link.vue';
import Heart from '@/icons/Heart.vue';
import ExternalLinkButton from '../ui/ExternalLinkButton.vue';

const page = usePage();

const metadata = computed(() => page.props.app?.metadata);
</script>

<template>
    <div class="flex h-full flex-col gap-5">
        <div class="flex items-center gap-3">
            <Avatar class="h-10 w-10">MN</Avatar>
            <span class="flex min-w-0 flex-col gap-0.5">
                <span class="flex items-center gap-2.5">
                    <span class="text-light-base-950/80 dark:text-base-50 font-semibold">
                        Many Notes
                    </span>
                </span>
                <span class="text-light-base-700 dark:text-base-200 inline-flex gap-2 text-xs">
                    v{{ metadata?.app_version }}
                    <span
                        v-if="metadata?.update_available"
                        class="text-success-600 dark:text-success-500 flex items-center gap-1.5"
                    >
                        <span class="size-[5px] rounded-full bg-current"></span>
                        <span>v{{ metadata?.latest_version }} available</span>
                    </span>
                </span>
            </span>
        </div>

        <p>
            Follow the development and report any issues on GitHub. If you find this project useful,
            consider supporting its development.
        </p>

        <div class="mt-auto flex flex-col gap-5">
            <ExternalLinkButton
                v-if="metadata?.github_url"
                text="Source and issues"
                :link="metadata?.github_url"
                :icon="Link"
                class="text-primary-300 dark:text-primary-600"
            />
            <ExternalLinkButton
                v-for="(value, key, index) in metadata?.sponsor_urls"
                :key="'sponsor_url_' + index"
                :text="key"
                :link="value"
                :icon="Heart"
                class="text-error-500 dark:text-error-400"
            />
        </div>
    </div>
</template>
