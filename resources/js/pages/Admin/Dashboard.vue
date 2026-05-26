<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '../../layouts/AppLayout.vue';
import VueApexCharts from 'vue3-apexcharts';

const { t } = useI18n();

const props = defineProps({
    stats: Object,
    userGrowth: Object,
    tasksByStatus: Object,
    recentUsers: Array,
    recentLogs: Array,
});

const statCards = computed(() => [
    { label: 'Users', value: props.stats.total_users, icon: 'users', color: 'bg-blue-500' },
    { label: 'Projects', value: props.stats.total_projects, icon: 'folder', color: 'bg-teal-500' },
    { label: 'Tasks', value: props.stats.total_tasks, icon: 'tasks', color: 'bg-amber-500' },
    { label: 'Notes', value: props.stats.total_notes, icon: 'notes', color: 'bg-purple-500' },
    { label: 'New This Month', value: props.stats.new_users_month, icon: 'new', color: 'bg-green-500' },
    { label: 'Overdue Tasks', value: props.stats.overdue_tasks, icon: 'overdue', color: 'bg-red-500' },
]);

const userGrowthOptions = computed(() => ({
    chart: { type: 'area', height: 300, toolbar: { show: false } },
    colors: ['#14b8a6'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
    xaxis: { categories: Object.keys(props.userGrowth) },
    yaxis: { title: { text: 'New Users' } },
}));

const userGrowthSeries = computed(() => [
    { name: 'Users', data: Object.values(props.userGrowth) },
]);

const taskStatusOptions = computed(() => ({
    chart: { type: 'donut', height: 300 },
    labels: Object.keys(props.tasksByStatus),
    colors: ['#14b8a6', '#f59e0b', '#6366f1', '#ef4444', '#8b5cf6'],
    legend: { position: 'bottom' },
}));

const taskStatusSeries = computed(() => Object.values(props.tasksByStatus));
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
                <div class="flex gap-2">
                    <Link href="/admin/users" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm hover:bg-primary-700">Manage Users</Link>
                    <Link href="/admin/audit-logs" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm hover:bg-gray-700">Audit Logs</Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                <div
                    v-for="stat in statCards"
                    :key="stat.label"
                    class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700"
                >
                    <div :class="['w-10 h-10 rounded-lg flex items-center justify-center text-white mb-3', stat.color]">
                        <span class="text-lg font-bold">{{ stat.value }}</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ stat.label }}</p>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Growth (6 Months)</h3>
                    <VueApexCharts
                        v-if="Object.keys(userGrowth).length"
                        type="area"
                        :options="userGrowthOptions"
                        :series="userGrowthSeries"
                        height="300"
                    />
                    <p v-else class="text-gray-500 dark:text-gray-400 text-center py-12">No data yet</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tasks by Status</h3>
                    <VueApexCharts
                        v-if="Object.keys(tasksByStatus).length"
                        type="donut"
                        :options="taskStatusOptions"
                        :series="taskStatusSeries"
                        height="300"
                    />
                    <p v-else class="text-gray-500 dark:text-gray-400 text-center py-12">No tasks yet</p>
                </div>
            </div>

            <!-- Recent Users & Audit Logs -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Users</h3>
                        <Link href="/admin/users" class="text-sm text-primary-600 hover:underline">View All</Link>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div v-for="user in recentUsers" :key="user.id" class="p-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                            </div>
                            <span v-if="user.is_admin" class="px-2 py-1 text-xs bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400 rounded-full">Admin</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
                        <Link href="/admin/audit-logs" class="text-sm text-primary-600 hover:underline">View All</Link>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div v-for="log in recentLogs" :key="log.id" class="p-4">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <span class="font-medium">{{ log.user?.name || 'System' }}</span>
                                    — {{ log.action }}
                                </p>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ new Date(log.created_at).toLocaleDateString() }}</span>
                            </div>
                            <p v-if="log.model_type" class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ log.model_type }} #{{ log.model_id }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
