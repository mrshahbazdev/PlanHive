import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useNotificationStore = defineStore('notifications', () => {
    const unreadCount = ref(0);
    const toasts = ref([]);

    const fetchUnreadCount = async () => {
        try {
            const response = await axios.get('/api/notifications/unread-count');
            unreadCount.value = response.data.count;
        } catch {
            // silently fail
        }
    };

    const addToast = (notification) => {
        const id = Date.now();
        toasts.value.push({ id, ...notification });
        setTimeout(() => {
            toasts.value = toasts.value.filter(t => t.id !== id);
        }, 5000);
    };

    const removeToast = (id) => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    };

    return { unreadCount, toasts, fetchUnreadCount, addToast, removeToast };
});
