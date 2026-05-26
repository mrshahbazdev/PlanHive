<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../../layouts/AppLayout.vue';

const props = defineProps({
    logs: Object,
    actions: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const action = ref(props.filters.action || '');

let debounce;
watch(search, (val) => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get('/admin/audit-logs', { search: val, action: action.value }, { preserveState: true });
    }, 300);
});

const filterByAction = (a) => {
    action.value = a;
    router.get('/admin/audit-logs', { search: search.value, action: a }, { preserveState: true });
};
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Logs</h1>
                <Link href="/admin" class="text-sm text-primary-600 hover:underline">← Back to Admin</Link>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-4 mb-6 flex-wrap">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search logs..."
                    class="flex-1 max-w-md px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500"
                />
                <select
                    v-model="action"
                    @change="filterByAction(action)"
                    class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100"
                >
                    <option value="">All Actions</option>
                    <option v-for="a in actions" :key="a" :value="a">{{ a }}</option>
                </select>
            </div>

            <!-- Logs Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Target</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">IP</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ log.user?.name || 'System' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">{{ log.action }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <span v-if="log.model_type">{{ log.model_type }} #{{ log.model_id }}</span>
                                <span v-else>—</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ log.ip_address || '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ new Date(log.created_at).toLocaleString() }}</td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="!logs.data.length" class="p-8 text-center text-gray-500 dark:text-gray-400">No audit logs found</div>
            </div>

            <!-- Pagination -->
            <div v-if="logs.links && logs.links.length > 3" class="flex justify-center gap-1 mt-6">
                <Link
                    v-for="link in logs.links"
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
