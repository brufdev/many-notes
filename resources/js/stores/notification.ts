import { AppNotification } from '@/types';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notification', () => {
    const items = ref<AppNotification[]>([]);

    function setNotifications(notifications: AppNotification[]) {
        items.value = notifications;
    }

    function addNotification(notification: AppNotification) {
        items.value.unshift(notification);
    }

    function removeNotification(id: string) {
        items.value = items.value.filter(value => value.id !== id);
    }

    return { items, setNotifications, addNotification, removeNotification };
});
