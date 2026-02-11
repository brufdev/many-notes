<script setup lang="ts">
import Menu from '@/components/menu/Menu.vue';
import VaultCollaborationInvitationReceived from '@/components/notification/VaultCollaborationInvitationReceived.vue';
import Bell from '@/icons/Bell.vue';
import { useNotificationStore } from '@/stores/notification';
import { type Component } from 'vue';

const notificationStore = useNotificationStore();

const notificationComponents: Record<string, Component> = {
    VaultCollaborationInvitationReceived: VaultCollaborationInvitationReceived,
};
</script>

<template>
    <Menu type="dropdown">
        <template #trigger>
            <Bell class="h-5 w-5" />

            <span
                v-if="notificationStore.items.length > 0"
                class="ring-error-400 bg-error-600 absolute top-0 right-0.5 block h-1.5 w-1.5 animate-ping rounded-full"
            ></span>
        </template>

        <template #default="{ closeMenu }">
            <div class="min-w-[15rem]">
                <div v-if="notificationStore.items.length > 0">
                    <component
                        :is="notificationComponents[notification.type]"
                        v-for="notification in notificationStore.items"
                        :key="notification.id"
                        :notification="notification"
                        :on-click="closeMenu"
                    />
                </div>
                <div v-else class="px-3 text-sm">No notifications</div>
            </div>
        </template>
    </Menu>
</template>
