<script setup lang="ts">
import Menu from '@/components/menu/Menu.vue';
import MenuDivider from '@/components/menu/MenuDivider.vue';
import MenuItem from '@/components/menu/MenuItem.vue';
import AboutModal from '@/components/modal/AboutModal.vue';
import HelpModal from '@/components/modal/HelpModal.vue';
import PasswordEditModal from '@/components/modal/PasswordEditModal.vue';
import ProfileEditModal from '@/components/modal/ProfileEditModal.vue';
import SettingEditModal from '@/components/modal/SettingEditModal.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useTheme } from '@/composables/useTheme';
import ArrowUpTray from '@/icons/ArrowUpTray.vue';
import CircleStack from '@/icons/CircleStack.vue';
import Cog6Tooth from '@/icons/Cog6Tooth.vue';
import InformationCircle from '@/icons/InformationCircle.vue';
import Lock from '@/icons/Lock.vue';
import MessageCircleQuestionMark from '@/icons/MessageCircleQuestionMark.vue';
import Moon from '@/icons/Moon.vue';
import User from '@/icons/User.vue';
import { logout } from '@/routes/index';
import { index } from '@/routes/vaults';
import { useSettingStore } from '@/stores/setting';
import { useUserStore } from '@/stores/user';
import { AppPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = usePage().props as AppPageProps;
const { isDark, toggleTheme } = useTheme();
const { openModal } = useModalManager();
const settingStore = useSettingStore();
const userStore = useUserStore();

const user = computed(() => props.app?.user);
const metadata = computed(() => props.app?.metadata);
</script>

<template>
    <Menu type="dropdown">
        <template #trigger>
            <User class="h-5 w-5" />

            <span
                v-if="metadata?.update_available"
                class="bg-success-600 absolute top-0 right-0.5 block h-2 w-2 rounded-full"
            ></span>
        </template>

        <template #default="{ closeMenu }">
            <div class="min-w-[12rem]">
                <div class="px-3 text-base font-semibold">
                    {{ userStore.name }}
                </div>

                <div
                    v-if="user?.role === 'SUPER_ADMIN'"
                    class="text-light-base-700 dark:text-base-400 px-3 text-xs"
                >
                    Super Admin
                </div>
                <div
                    v-else-if="user?.role === 'ADMIN'"
                    class="text-light-base-700 dark:text-base-400 px-3 text-xs"
                >
                    Admin
                </div>

                <MenuDivider />

                <MenuItem
                    label="Profile"
                    :icon="User"
                    @click="
                        () => {
                            closeMenu();
                            openModal(ProfileEditModal, { title: 'Profile' });
                        }
                    "
                />

                <template v-if="settingStore.local_auth_enabled">
                    <MenuItem
                        label="Password"
                        :icon="Lock"
                        @click="
                            () => {
                                closeMenu();
                                openModal(PasswordEditModal, { title: 'Password' });
                            }
                        "
                    />
                </template>

                <MenuItem label="Vaults" :icon="CircleStack" :href="index.url()" />

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
                    label="Settings"
                    :icon="Cog6Tooth"
                    @click="
                        () => {
                            closeMenu();
                            openModal(SettingEditModal, { title: 'Settings' });
                        }
                    "
                />

                <MenuDivider />

                <MenuItem
                    label="Help"
                    :icon="MessageCircleQuestionMark"
                    @click="
                        () => {
                            closeMenu();
                            openModal(HelpModal, { title: 'Help' });
                        }
                    "
                />

                <MenuItem
                    label="About"
                    :icon="InformationCircle"
                    @click="
                        () => {
                            closeMenu();
                            openModal(AboutModal, { title: 'About' });
                        }
                    "
                />

                <MenuItem
                    label="Logout"
                    :icon="ArrowUpTray"
                    icon-class="rotate-90"
                    :href="logout.url()"
                    method="post"
                />
            </div>
        </template>
    </Menu>
</template>
