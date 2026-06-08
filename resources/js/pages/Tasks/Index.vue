<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({ tasks: Array, projects: Array });

const filter = ref('all');
const showCreateModal = ref(false);
const showEditModal = ref(false);

const createForm = useForm({
    title: '', description: '', priority: 'medium', status: 'todo', due_date: '', assigned_to: null,
});

const editForm = useForm({
    title: '', description: '', priority: 'medium', status: 'todo', due_date: '', assigned_to: null,
});

const selectedProject = ref(null);
const editingTask = ref(null);

const openCreate = () => {
    createForm.reset();
    selectedProject.value = props.projects?.[0]?.id || null;
    showCreateModal.value = true;
};

const createTask = () => {
    if (!selectedProject.value) return;
    createForm.post(`/projects/${selectedProject.value}/tasks`, {
        onSuccess: () => { showCreateModal.value = false; createForm.reset(); },
    });
};

const openEdit = (task) => {
    editingTask.value = task;
    editForm.title = task.title;
    editForm.description = task.description || '';
    editForm.priority = task.priority;
    editForm.status = task.status;
    editForm.due_date = task.due_date ? task.due_date.substring(0, 10) : '';
    editForm.assigned_to = task.assigned_to;
    showEditModal.value = true;
};

const updateTask = () => {
    editForm.put(`/tasks/${editingTask.value.id}`, {
        onSuccess: () => { showEditModal.value = false; editingTask.value = null; },
    });
};

const deleteTask = (taskId) => {
    if (confirm(t('common.confirm_delete'))) {
        router.delete(`/tasks/${taskId}`);
    }
};

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
            <div class="flex items-center gap-3">
                <div class="flex gap-2">
                    <button v-for="f in ['all', 'todo', 'in_progress', 'review', 'done']" :key="f"
                            @click="filter = f"
                            :class="['px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                                     filter === f ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400']">
                        {{ f === 'all' ? 'All' : t(`tasks.${f}`) }}
                    </button>
                </div>
                <button @click="openCreate" class="btn-primary text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ t('tasks.new') }}
                </button>
            </div>
        </div>

        <div v-if="tasks.length === 0" class="card p-12 text-center">
            <p class="text-gray-500 mb-4">{{ t('tasks.no_tasks') }}</p>
            <button @click="openCreate" class="btn-primary text-sm">{{ t('tasks.new') }}</button>
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
                <button @click="openEdit(task)" class="text-gray-400 hover:text-primary-500 p-1" :title="t('common.edit')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button @click="deleteTask(task.id)" class="text-gray-400 hover:text-red-500 p-1" :title="t('common.delete')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>

        <!-- Create Task Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showCreateModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('tasks.new') }}</h3>
                <form @submit.prevent="createTask" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('nav.projects') }}</label>
                        <select v-model="selectedProject" required class="input-field">
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.task_title') }}</label>
                        <input v-model="createForm.title" type="text" required class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.description') }}</label>
                        <textarea v-model="createForm.description" rows="2" class="input-field"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.priority') }}</label>
                            <select v-model="createForm.priority" class="input-field">
                                <option value="low">{{ t('tasks.low') }}</option>
                                <option value="medium">{{ t('tasks.medium') }}</option>
                                <option value="high">{{ t('tasks.high') }}</option>
                                <option value="urgent">{{ t('tasks.urgent') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.due_date') }}</label>
                            <input v-model="createForm.due_date" type="date" class="input-field" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showCreateModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="createForm.processing" class="btn-primary">{{ t('common.create') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Task Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showEditModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('common.edit') }} {{ t('tasks.title') }}</h3>
                <form @submit.prevent="updateTask" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.task_title') }}</label>
                        <input v-model="editForm.title" type="text" required class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.description') }}</label>
                        <textarea v-model="editForm.description" rows="2" class="input-field"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.priority') }}</label>
                            <select v-model="editForm.priority" class="input-field">
                                <option value="low">{{ t('tasks.low') }}</option>
                                <option value="medium">{{ t('tasks.medium') }}</option>
                                <option value="high">{{ t('tasks.high') }}</option>
                                <option value="urgent">{{ t('tasks.urgent') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.status') }}</label>
                            <select v-model="editForm.status" class="input-field">
                                <option v-for="s in ['todo', 'in_progress', 'review', 'done', 'cancelled']" :key="s" :value="s">{{ t(`tasks.${s}`) }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.due_date') }}</label>
                        <input v-model="editForm.due_date" type="date" class="input-field" />
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
