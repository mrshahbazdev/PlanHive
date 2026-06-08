<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({ goals: Object, projects: Array });

const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedProject = ref(null);
const editingGoal = ref(null);

const createForm = useForm({
    title: '', description: '', target_date: '',
});

const editForm = useForm({
    title: '', description: '', target_date: '', progress: 0, status: 'not_started',
});

const openCreate = () => {
    createForm.reset();
    selectedProject.value = props.projects?.[0]?.id || null;
    showCreateModal.value = true;
};

const createGoal = () => {
    if (!selectedProject.value) return;
    createForm.post(`/projects/${selectedProject.value}/goals`, {
        onSuccess: () => { showCreateModal.value = false; createForm.reset(); },
    });
};

const openEdit = (goal) => {
    editingGoal.value = goal;
    editForm.title = goal.title;
    editForm.description = goal.description || '';
    editForm.target_date = goal.target_date ? goal.target_date.substring(0, 10) : '';
    editForm.progress = goal.progress;
    editForm.status = goal.status;
    showEditModal.value = true;
};

const updateGoal = () => {
    editForm.put(`/goals/${editingGoal.value.id}`, {
        onSuccess: () => { showEditModal.value = false; editingGoal.value = null; },
    });
};

const updateProgress = (goalId, progress) => {
    router.put(`/goals/${goalId}`, { progress: parseInt(progress) }, { preserveScroll: true });
};

const deleteGoal = (goalId) => {
    if (confirm(t('common.confirm_delete'))) {
        router.delete(`/goals/${goalId}`);
    }
};

const statusColors = {
    not_started: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    in_progress: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    achieved: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    missed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('goals.title') }}</h1>
            <button @click="openCreate" class="btn-primary text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ t('goals.new') }}
            </button>
        </div>

        <div v-if="!goals?.data?.length" class="card p-12 text-center">
            <p class="text-gray-500 mb-4">{{ t('goals.no_goals') }}</p>
            <button @click="openCreate" class="btn-primary text-sm">{{ t('goals.new') }}</button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-for="goal in goals.data" :key="goal.id" class="card p-6">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ goal.title }}</h3>
                        <p v-if="goal.project" class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: goal.project.color }"></span>
                            {{ goal.project.name }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span :class="[statusColors[goal.status], 'text-xs px-2.5 py-1 rounded-full font-medium']">{{ t(`goals.${goal.status}`) }}</span>
                        <button @click="openEdit(goal)" class="text-gray-400 hover:text-primary-500 p-1" :title="t('common.edit')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button @click="deleteGoal(goal.id)" class="text-gray-400 hover:text-red-500 p-1" :title="t('common.delete')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
                <p v-if="goal.description" class="text-sm text-gray-500 mb-3">{{ goal.description }}</p>
                <div class="flex items-center gap-3 mb-2">
                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-primary-500 h-2 rounded-full transition-all" :style="{ width: `${goal.progress}%` }"></div>
                    </div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-10 text-right">{{ goal.progress }}%</span>
                </div>
                <div class="flex items-center gap-2">
                    <input type="range" min="0" max="100" step="5" :value="goal.progress"
                           @change="updateProgress(goal.id, $event.target.value)"
                           class="flex-1 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary-500" />
                </div>
                <p v-if="goal.target_date" class="text-xs text-gray-400 mt-2">{{ t('goals.target_date') }}: {{ new Date(goal.target_date).toLocaleDateString() }}</p>
            </div>
        </div>

        <!-- Create Goal Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showCreateModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('goals.new') }}</h3>
                <form @submit.prevent="createGoal" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('nav.projects') }}</label>
                        <select v-model="selectedProject" required class="input-field">
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('goals.goal_title') }}</label>
                        <input v-model="createForm.title" type="text" required class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.description') }}</label>
                        <textarea v-model="createForm.description" rows="2" class="input-field"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('goals.target_date') }}</label>
                        <input v-model="createForm.target_date" type="date" class="input-field" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showCreateModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="createForm.processing" class="btn-primary">{{ t('common.create') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Goal Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showEditModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('common.edit') }} {{ t('goals.title') }}</h3>
                <form @submit.prevent="updateGoal" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('goals.goal_title') }}</label>
                        <input v-model="editForm.title" type="text" required class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.description') }}</label>
                        <textarea v-model="editForm.description" rows="2" class="input-field"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('goals.target_date') }}</label>
                        <input v-model="editForm.target_date" type="date" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('goals.progress') }} ({{ editForm.progress }}%)</label>
                        <input type="range" v-model.number="editForm.progress" min="0" max="100" step="5"
                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.status') }}</label>
                        <select v-model="editForm.status" class="input-field">
                            <option v-for="s in ['not_started', 'in_progress', 'achieved', 'missed']" :key="s" :value="s">{{ t(`goals.${s}`) }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showEditModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="editForm.processing" class="btn-primary">{{ t('common.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
