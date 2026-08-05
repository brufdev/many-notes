<script setup lang="ts">
import Menu from '@/components/menu/Menu.vue';
import MenuDivider from '@/components/menu/MenuDivider.vue';
import MenuItem from '@/components/menu/MenuItem.vue';
import AboutModal from '@/components/modal/AboutModal.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useTheme } from '@/composables/useTheme';
import Cog6Tooth from '@/icons/Cog6Tooth.vue';
import InformationCircle from '@/icons/InformationCircle.vue';
import Moon from '@/icons/Moon.vue';

const { isDark, toggleTheme } = useTheme();
const { openModal } = useModalManager();
</script>

<template>
    <Menu type="dropdown">
        <template #trigger>
            <Cog6Tooth class="h-5 w-5" />
        </template>

        <template #default="{ closeMenu }">
            <div class="min-w-[12rem]">
                <MenuItem @click="toggleTheme">
                    <span class="flex w-full items-center justify-between">
                        <span class="flex items-center gap-2">
                            <Moon class="h-4 w-4" />
                            Dark mode
                        </span>
                        <span
                            class="relative inline-flex h-5 w-10 items-center rounded-full transition-colors"
                            :class="
                                isDark
                                    ? 'bg-primary-300 dark:bg-primary-600'
                                    : 'bg-gray-200 dark:bg-gray-700'
                            "
                        >
                            <span
                                class="absolute h-4.5 w-4.5 transform rounded-full border border-gray-300 bg-white transition-all"
                                :class="
                                    isDark
                                        ? 'translate-x-5 border-white rtl:-translate-x-5'
                                        : 'translate-x-0 rtl:translate-x-0 dark:border-gray-600'
                                "
                            ></span>
                        </span>
                    </span>
                </MenuItem>

                <MenuDivider />

                <MenuItem
                    label="About"
                    :icon="InformationCircle"
                    @click="
                        closeMenu();
                        openModal(AboutModal, { title: 'About' });
                    "
                />
            </div>
        </template>
    </Menu>
</template>
