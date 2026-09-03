<script setup lang="ts">
import MenuItem from '@/components/menu/MenuItem.vue';
import { CollaborationConfirmModal } from '@/components/modal';
import { useModalManager } from '@/composables';
import { AppNotification } from '@/types';

defineProps<{
    notification: AppNotification;
    onClick?: () => void;
}>();

const { openModal } = useModalManager();
</script>

<template>
    <MenuItem
        :label="`${notification.data.user_name} invited you to collaborate`"
        @click="
            openModal(CollaborationConfirmModal, {
                notification: notification,
            });
            onClick?.();
        "
    />
</template>
