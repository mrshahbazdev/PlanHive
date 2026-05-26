<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
defineProps({ tasks: Array });

const filter = ref('all');

const updateStatus = (taskId, status) => {
    router.patch(`/tasks/${taskId}/status`, { status });
};

const priorityColors = {
    urgent: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    high: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    medium: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    low: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
};

const statusColors = {
    todo: 'bg-gray-400', in_progress: 'bg-blue-500', review: 'bg-amber-500', done: 'bg-green-500', cancelled: 'bg-red-400',
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('tasks.title') }}</h1>
            <div class="flex gap-2">
                <button v-for="f in ['all', 'todo', 'in_progress', 'review', 'done']" :key="f"
                        @click="filter = f"
                        :class="['px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                                 filter === f ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400']">
                    {{ f === 'all' ? 'All' : t(`tasks.${f}`) }}
                </button>
            </div>
        </div>

        <div v-if="tasks.length === 0" class="card p-12 text-center">
            <p class="text-gray-500">{{ t('tasks.no_tasks') }}</p>
        </div>

        <div v-else class="space-y-2">
            <div v-for="task in tasks.filter(t => filter === 'all' || t.status === filter)" :key="task.id"
                 class="card p-4 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div :class="[statusColors[task.status], 'w-3 h-3 rounded-full flex-shrink-0']"></div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ task.title }}</p>
                    <div class="flex items-center gap-3 mt-1">
                        <span v-if="task.project" class="flex items-center gap-1 text-xs text-gray-500">
                            <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: task.project.color }"></span>
                            {{ task.project.name }}
                        </span>
                        <span v-if="task.due_date" class="text-xs text-gray-400">{{ new Date(task.due_date).toLocaleDateString() }}</span>
                    </div>
                </div>
                <span :class="[priorityColors[task.priority], 'text-xs px-2.5 py-1 rounded-full font-medium']">{{ t(`tasks.${task.priority}`) }}</span>
                <select @change="updateStatus(task.id, $event.target.value)" :value="task.status" class="text-xs border rounded-lg px-2 py-1 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600">
                    <option v-for="s in ['todo', 'in_progress', 'review', 'done', 'cancelled']" :key="s" :value="s">{{ t(`tasks.${s}`) }}</option>
                </select>
            </div>
        </div>
    </div>
</template>
