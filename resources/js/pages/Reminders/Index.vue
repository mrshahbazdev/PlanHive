<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
defineProps({ reminders: Object });

const showModal = ref(false);

const form = useForm({
    title: '',
    remind_at: '',
    channel: 'in_app',
});

const createReminder = () => {
    form.post('/reminders', {
        onSuccess: () => { showModal.value = false; form.reset(); },
    });
};

const deleteReminder = (id) => {
    if (confirm(t('common.confirm_delete'))) {
        router.delete(`/reminders/${id}`);
    }
};

const isPast = (date) => new Date(date) < new Date();

const formatDate = (date) => {
    return new Date(date).toLocaleString(undefined, {
        dateStyle: 'medium', timeStyle: 'short',
    });
};
</script>

<template>
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reminders</h1>
            <button @click="showModal = true" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Reminder
            </button>
        </div>

        <div v-if="!reminders?.data?.length" class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-gray-500">No reminders set</p>
        </div>

        <div v-else class="space-y-2">
            <div v-for="reminder in reminders.data" :key="reminder.id"
                 :class="['card p-4 flex items-center gap-4', isPast(reminder.remind_at) ? 'opacity-60' : '']">
                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0',
                              isPast(reminder.remind_at) ? 'bg-gray-100 dark:bg-gray-700' : 'bg-amber-100 dark:bg-amber-900/30']">
                    <svg :class="['w-5 h-5', isPast(reminder.remind_at) ? 'text-gray-400' : 'text-amber-600 dark:text-amber-400']" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ reminder.title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ formatDate(reminder.remind_at) }}</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">{{ reminder.channel }}</span>
                <button @click="deleteReminder(reminder.id)" class="text-gray-400 hover:text-red-500 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>

        <!-- Create Reminder Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">New Reminder</h3>
                <form @submit.prevent="createReminder" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                        <input v-model="form.title" type="text" required class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Remind At</label>
                        <input v-model="form.remind_at" type="datetime-local" required class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Channel</label>
                        <select v-model="form.channel" class="input-field">
                            <option value="in_app">In-App</option>
                            <option value="email">Email</option>
                            <option value="push">Push</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="form.processing" class="btn-primary">{{ t('common.create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
