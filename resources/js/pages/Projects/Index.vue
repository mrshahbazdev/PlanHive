<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({ projects: Array });

const statusColors = {
    active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    on_hold: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    completed: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    archived: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('projects.title') }}</h1>
            <Link href="/projects/create" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ t('projects.new') }}
            </Link>
        </div>

        <div v-if="projects.length === 0" class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            <p class="text-gray-500 dark:text-gray-400">{{ t('projects.no_projects') }}</p>
            <Link href="/projects/create" class="btn-primary mt-4 inline-flex">{{ t('projects.new') }}</Link>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link v-for="project in projects" :key="project.id" :href="`/projects/${project.id}`"
                  class="card p-6 hover:shadow-md transition-shadow group">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: project.color }"></div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-600">{{ project.name }}</h3>
                    </div>
                    <span :class="[statusColors[project.status], 'text-xs px-2.5 py-1 rounded-full font-medium']">
                        {{ t(`projects.${project.status}`) }}
                    </span>
                </div>
                <p v-if="project.description" class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-4">{{ project.description }}</p>
                <div class="flex items-center gap-4 text-xs text-gray-400">
                    <span>{{ project.tasks_count || 0 }} {{ t('nav.tasks') }}</span>
                    <span>{{ project.members_count || 0 }} {{ t('projects.members') }}</span>
                    <span>{{ project.goals_count || 0 }} {{ t('nav.goals') }}</span>
                </div>
            </Link>
        </div>
    </div>
</template>
