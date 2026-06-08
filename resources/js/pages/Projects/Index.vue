<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({ projects: Array, filters: Object });

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');

const statuses = ['all', 'active', 'on_hold', 'completed', 'archived'];

const filteredProjects = computed(() => {
    let list = props.projects || [];
    if (statusFilter.value !== 'all') {
        list = list.filter(p => p.status === statusFilter.value);
    }
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(p => p.name.toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q));
    }
    return list;
});

const statusColors = {
    active: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    on_hold: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    completed: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    archived: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
};

const taskProgress = (project) => {
    const total = project.tasks_count || 0;
    const done = project.done_tasks_count || 0;
    if (total === 0) return 0;
    return Math.round((done / total) * 100);
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

        <!-- Search & Filter Bar -->
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="relative flex-1 max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input v-model="search" type="text" :placeholder="t('common.search')" class="input-field pl-10" />
            </div>
            <div class="flex gap-2">
                <button
                    v-for="st in statuses" :key="st"
                    @click="statusFilter = st"
                    :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-colors capitalize',
                        statusFilter === st
                            ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400'
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600']"
                >
                    {{ st === 'all' ? t('common.all') : t(`projects.${st}`) }}
                </button>
            </div>
        </div>

        <div v-if="filteredProjects.length === 0" class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            <p class="text-gray-500 dark:text-gray-400">{{ t('projects.no_projects') }}</p>
            <Link href="/projects/create" class="btn-primary mt-4 inline-flex">{{ t('projects.new') }}</Link>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link v-for="project in filteredProjects" :key="project.id" :href="`/projects/${project.id}`"
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

                <!-- Progress Bar -->
                <div v-if="project.tasks_count > 0" class="mb-3">
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                        <span>{{ t('tasks.progress') || 'Progress' }}</span>
                        <span>{{ project.done_tasks_count || 0 }}/{{ project.tasks_count }} ({{ taskProgress(project) }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                        <div class="bg-primary-500 h-1.5 rounded-full transition-all" :style="{ width: taskProgress(project) + '%' }"></div>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-xs text-gray-400">
                    <span>{{ project.tasks_count || 0 }} {{ t('nav.tasks') }}</span>
                    <span>{{ project.members_count || 0 }} {{ t('projects.members') }}</span>
                    <span>{{ project.goals_count || 0 }} {{ t('nav.goals') }}</span>
                </div>
            </Link>
        </div>
    </div>
</template>
