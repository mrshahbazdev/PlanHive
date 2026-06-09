<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '../../../layouts/AppLayout.vue';

const { t } = useI18n();

const props = defineProps({
    user: Object,
    recentActivity: Array,
});

const form = ref({
    name: props.user.name,
    email: props.user.email,
    is_admin: props.user.is_admin,
});

const editing = ref(false);

const save = () => {
    router.put(`/admin/users/${props.user.id}`, form.value, {
        onSuccess: () => { editing.value = false; },
    });
};
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('admin.user') }}: {{ user.name }}</h1>
                <Link href="/admin/users" class="text-sm text-primary-600 hover:underline">← {{ t('admin.back_to_users') }}</Link>
            </div>

            <!-- User Info Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('admin.profile') }}</h3>
                    <button @click="editing = !editing" class="text-sm text-primary-600 hover:underline">
                        {{ editing ? t('common.cancel') : t('common.edit') }}
                    </button>
                </div>

                <div v-if="!editing" class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('auth.name') }}</span>
                        <span class="text-sm text-gray-900 dark:text-white">{{ user.name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('common.email') }}</span>
                        <span class="text-sm text-gray-900 dark:text-white">{{ user.email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('admin.role') }}</span>
                        <span :class="['px-2 py-1 text-xs rounded-full', user.is_admin ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
                            {{ user.is_admin ? t('admin.admin') : t('admin.user') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('admin.joined') }}</span>
                        <span class="text-sm text-gray-900 dark:text-white">{{ new Date(user.created_at).toLocaleDateString() }}</span>
                    </div>
                </div>

                <form v-else @submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('auth.name') }}</label>
                        <input v-model="form.name" type="text" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('common.email') }}</label>
                        <input v-model="form.email" type="email" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input v-model="form.is_admin" type="checkbox" id="is_admin" class="rounded border-gray-300" />
                        <label for="is_admin" class="text-sm text-gray-700 dark:text-gray-300">{{ t('admin.admin') }}</label>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm hover:bg-primary-700">{{ t('admin.save_changes') }}</button>
                </form>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.owned_projects_count }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('nav.projects') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.tasks_count }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('nav.tasks') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.notes_count }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('nav.notes') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.contacts_count }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('nav.contacts') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.reminders_count }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('nav.reminders') }}</p>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('admin.recent_activity') }}</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div v-for="log in recentActivity" :key="log.id" class="p-4 flex justify-between">
                        <div>
                            <p class="text-sm text-gray-900 dark:text-white">{{ log.action }}</p>
                            <p v-if="log.model_type" class="text-xs text-gray-500 dark:text-gray-400">{{ log.model_type }} #{{ log.model_id }}</p>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ new Date(log.created_at).toLocaleString() }}</span>
                    </div>
                    <div v-if="!recentActivity.length" class="p-8 text-center text-gray-500 dark:text-gray-400">{{ t('admin.no_activity') }}</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
