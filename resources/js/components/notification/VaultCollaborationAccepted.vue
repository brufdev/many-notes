<script setup lang="ts">
import { destroy } from '@/actions/App/Http/Controllers/NotificationController';
import MenuItem from '@/components/menu/MenuItem.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useToast } from '@/composables/useToast';
import { useNotificationStore } from '@/stores/notification';
import { AppNotification } from '@/types';

const props = defineProps<{
    notification: AppNotification;
    onClick?: () => void;
}>();

const { removeNotification } = useNotificationStore();
const { createToast } = useToast();

const form = useAxiosForm({});

const handleClick = () => {
    form.send({
        url: destroy.url({ notification: props.notification.id }),
        method: 'delete',
        onError: error => {
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
            props.onClick?.();
        },
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
        :label="`${notification.data.user_name} accepted collaborating`"
        title="Click to dismiss"
        @click="handleClick"
    />
</template>
