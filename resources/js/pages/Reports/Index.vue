<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLayout from '../../layouts/AppLayout.vue';
import VueApexCharts from 'vue3-apexcharts';

const { t } = useI18n();

const props = defineProps({
    projects: Array,
    tasksByMonth: Object,
    tasksByPriority: Object,
    tasksByStatus: Object,
    upcomingDeadlines: Array,
    productivityScore: Number,
    summary: Object,
});

const completionOptions = computed(() => ({
    chart: { type: 'area', height: 280, toolbar: { show: false } },
    colors: ['#14b8a6'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
    xaxis: { categories: Object.keys(props.tasksByMonth) },
    yaxis: { title: { text: 'Completed' } },
}));
const completionSeries = computed(() => [{ name: 'Completed', data: Object.values(props.tasksByMonth) }]);

const priorityOptions = computed(() => ({
    chart: { type: 'bar', height: 280, toolbar: { show: false } },
    colors: ['#ef4444', '#f59e0b', '#3b82f6', '#6b7280'],
    plotOptions: { bar: { borderRadius: 6, horizontal: true } },
    xaxis: { categories: Object.keys(props.tasksByPriority) },
}));
const prioritySeries = computed(() => [{ name: 'Tasks', data: Object.values(props.tasksByPriority) }]);

const statusOptions = computed(() => ({
    chart: { type: 'donut', height: 280 },
    labels: Object.keys(props.tasksByStatus),
    colors: ['#14b8a6', '#f59e0b', '#6366f1', '#ef4444', '#8b5cf6'],
    legend: { position: 'bottom' },
}));
const statusSeries = computed(() => Object.values(props.tasksByStatus));

const scoreColor = computed(() => {
    if (props.productivityScore >= 75) return 'text-green-500';
    if (props.productivityScore >= 50) return 'text-amber-500';
    return 'text-red-500';
});

const formatDate = (date) => new Date(date).toLocaleDateString();

const priorityColor = (p) => {
    const map = { critical: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400', high: 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400', medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400', low: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' };
    return map[p] || map.low;
};
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-7xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">Reports & Analytics</h1>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.total_projects }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Projects</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.total_tasks }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Tasks</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-2xl font-bold text-green-600">{{ summary.completed_tasks }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-2xl font-bold text-red-600">{{ summary.overdue_tasks }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Overdue</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.total_goals }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Goals</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p :class="['text-2xl font-bold', scoreColor]">{{ productivityScore }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Productivity</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Task Completion (6 Months)</h3>
                    <VueApexCharts v-if="Object.keys(tasksByMonth).length" type="area" :options="completionOptions" :series="completionSeries" height="280" />
                    <p v-else class="text-gray-500 dark:text-gray-400 text-center py-12">No data yet</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tasks by Status</h3>
                    <VueApexCharts v-if="Object.keys(tasksByStatus).length" type="donut" :options="statusOptions" :series="statusSeries" height="280" />
                    <p v-else class="text-gray-500 dark:text-gray-400 text-center py-12">No data yet</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tasks by Priority</h3>
                    <VueApexCharts v-if="Object.keys(tasksByPriority).length" type="bar" :options="priorityOptions" :series="prioritySeries" height="280" />
                    <p v-else class="text-gray-500 dark:text-gray-400 text-center py-12">No data yet</p>
                </div>

                <!-- Project Progress -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Project Progress</h3>
                    <div class="space-y-4 max-h-[280px] overflow-y-auto">
                        <div v-for="project in projects" :key="project.id">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: project.color }"></span>
                                    {{ project.name }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ project.completed_tasks_count }}/{{ project.tasks_count }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div
                                    class="h-2 rounded-full transition-all duration-300"
                                    :style="{
                                        width: project.tasks_count ? (project.completed_tasks_count / project.tasks_count * 100) + '%' : '0%',
                                        backgroundColor: project.color || '#14b8a6',
                                    }"
                                ></div>
                            </div>
                        </div>
                        <p v-if="!projects.length" class="text-gray-500 dark:text-gray-400 text-center py-6">No projects yet</p>
                    </div>
                </div>
            </div>

            <!-- Upcoming Deadlines -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming Deadlines (14 Days)</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div v-for="task in upcomingDeadlines" :key="task.id" class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: task.project?.color || '#14b8a6' }"></span>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ task.title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ task.project?.name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span :class="['px-2 py-0.5 text-xs rounded-full', priorityColor(task.priority)]">{{ task.priority }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(task.due_date) }}</span>
                        </div>
                    </div>
                    <div v-if="!upcomingDeadlines.length" class="p-8 text-center text-gray-500 dark:text-gray-400">No upcoming deadlines</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
