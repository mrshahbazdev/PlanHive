<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '../../../layouts/AppLayout.vue';

const { t } = useI18n();

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const role = ref(props.filters.role || '');

let debounce;
watch(search, (val) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get('/admin/users', { search: val, role: role.value }, { preserveState: true });
    }, 300);
});

const filterByRole = (r) => {
    role.value = r;
    router.get('/admin/users', { search: search.value, role: r }, { preserveState: true });
};

const toggleAdmin = (user) => {
    if (confirm(`Toggle admin for ${user.name}?`)) {
        router.post(`/admin/users/${user.id}/toggle-admin`);
    }
};

const deleteUser = (user) => {
    if (confirm(`Delete ${user.name}? This cannot be undone.`)) {
        router.delete(`/admin/users/${user.id}`);
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Management</h1>
                <Link href="/admin" class="text-sm text-primary-600 hover:underline">← Back to Admin</Link>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-4 mb-6">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search users..."
                    class="flex-1 max-w-md px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500"
                />
                <div class="flex gap-2">
                    <button @click="filterByRole('')" :class="['px-3 py-1.5 rounded-lg text-sm', !role ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">All</button>
                    <button @click="filterByRole('admin')" :class="['px-3 py-1.5 rounded-lg text-sm', role === 'admin' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">Admins</button>
                    <button @click="filterByRole('user')" :class="['px-3 py-1.5 rounded-lg text-sm', role === 'user' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300']">Users</button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                            <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Projects</th>
                            <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tasks</th>
                            <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Notes</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Joined</th>
                            <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-6 py-4">
                                <Link :href="`/admin/users/${user.id}`" class="hover:underline">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                </Link>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 text-xs rounded-full', user.is_admin ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
                                    {{ user.is_admin ? 'Admin' : 'User' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">{{ user.owned_projects_count }}</td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">{{ user.tasks_count }}</td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-gray-300">{{ user.notes_count }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="toggleAdmin(user)" class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                                        {{ user.is_admin ? 'Remove Admin' : 'Make Admin' }}
                                    </button>
                                    <button @click="deleteUser(user)" class="text-xs px-2 py-1 rounded bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/40">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.links && users.links.length > 3" class="flex justify-center gap-1 mt-6">
                <Link
                    v-for="link in users.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    :class="['px-3 py-1.5 text-sm rounded', link.active ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700']"
                    v-html="link.label"
                    :disabled="!link.url"
                />
            </div>
        </div>
    </AppLayout>
</template>
