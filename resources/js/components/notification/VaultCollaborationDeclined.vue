<script setup lang="ts">
import { destroy } from '@/actions/App/Http/Controllers/NotificationController';
import MenuItem from '@/components/menu/MenuItem.vue';
import { useRequest } from '@/composables/useRequest';
import { useToast } from '@/composables/useToast';
import { useNotificationStore } from '@/stores/notification';
import { AppNotification } from '@/types';

const props = defineProps<{
    notification: AppNotification;
    onClick?: () => void;
}>();

const { removeNotification } = useNotificationStore();
const { createToast } = useToast();

const form = useRequest({});

const handleClick = () => {
    form.delete(destroy.url({ notification: props.notification.id }), {
        onFailure: () => props.onClick?.(),
        onSuccess: () => {
            removeNotification(props.notification.id);
            createToast('Notification deleted', 'success');
            props.onClick?.();
        },
    });
};
</script>

<template>
    <MenuItem
        :label="`${notification.data.user_name} declined collaborating`"
        title="Click to dismiss"
        @click="handleClick"
    />
</template>
