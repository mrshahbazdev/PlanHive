<script setup>
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
defineProps({ notifications: Object, unreadCount: Number });

const markAsRead = (id) => {
    router.patch(`/notifications/${id}/read`);
};

const markAllAsRead = () => {
    router.post('/notifications/read-all');
};

const timeAgo = (date) => {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
    return `${Math.floor(seconds / 86400)}d ago`;
};
</script>

<template>
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications</h1>
            <button v-if="unreadCount > 0" @click="markAllAsRead" class="btn-secondary text-sm">
                Mark all as read ({{ unreadCount }})
            </button>
        </div>

        <div v-if="!notifications?.data?.length" class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-gray-500">No notifications yet</p>
        </div>

        <div v-else class="space-y-2">
            <div v-for="notification in notifications.data" :key="notification.id"
                 :class="['card p-4 flex items-start gap-4 transition-colors', !notification.read_at ? 'bg-primary-50/50 dark:bg-primary-900/10 border-primary-200 dark:border-primary-800' : '']">
                <div :class="['w-2 h-2 rounded-full mt-2 flex-shrink-0', !notification.read_at ? 'bg-primary-500' : 'bg-gray-300']"></div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900 dark:text-white">{{ notification.data?.message || 'Notification' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ timeAgo(notification.created_at) }}</p>
                </div>
                <button v-if="!notification.read_at" @click="markAsRead(notification.id)"
                        class="text-xs text-primary-600 hover:text-primary-700 font-medium flex-shrink-0">
                    Mark read
                </button>
            </div>
        </div>
    </div>
</template>
