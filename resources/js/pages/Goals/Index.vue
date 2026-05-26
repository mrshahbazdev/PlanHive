<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
defineProps({ goals: Object });

const statusColors = {
    not_started: 'bg-gray-100 text-gray-700', in_progress: 'bg-blue-100 text-blue-700',
    achieved: 'bg-green-100 text-green-700', missed: 'bg-red-100 text-red-700',
};
</script>

<template>
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ t('goals.title') }}</h1>

        <div v-if="!goals?.data?.length" class="card p-12 text-center">
            <p class="text-gray-500">No goals yet. Create goals from your project pages.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-for="goal in goals.data" :key="goal.id" class="card p-6">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ goal.title }}</h3>
                        <p v-if="goal.project" class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: goal.project.color }"></span>
                            {{ goal.project.name }}
                        </p>
                    </div>
                    <span :class="[statusColors[goal.status], 'text-xs px-2.5 py-1 rounded-full font-medium']">{{ t(`goals.${goal.status}`) }}</span>
                </div>
                <p v-if="goal.description" class="text-sm text-gray-500 mb-3">{{ goal.description }}</p>
                <div class="flex items-center gap-3">
                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-primary-500 h-2 rounded-full transition-all" :style="{ width: `${goal.progress}%` }"></div>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ goal.progress }}%</span>
                </div>
                <p v-if="goal.target_date" class="text-xs text-gray-400 mt-2">{{ t('goals.target_date') }}: {{ new Date(goal.target_date).toLocaleDateString() }}</p>
            </div>
        </div>
    </div>
</template>
