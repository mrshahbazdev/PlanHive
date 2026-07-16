<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({ project: Object });
const page = usePage();
const user = page.props.auth.user;

const showTaskModal = ref(false);
const showGoalModal = ref(false);
const showMemberModal = ref(false);
const showEditTaskModal = ref(false);
const showEditGoalModal = ref(false);
const showTaskDetail = ref(false);
const activeTab = ref('tasks');
const editingTask = ref(null);
const editingGoal = ref(null);
const detailTask = ref(null);
const draggedTaskId = ref(null);

const taskForm = useForm({
    title: '', description: '', priority: 'medium', status: 'todo', due_date: '', assigned_to: user.id,
});

const editTaskForm = useForm({
    title: '', description: '', priority: 'medium', status: 'todo', due_date: '', assigned_to: user.id,
});

const goalForm = useForm({
    title: '', description: '', target_date: '',
});

const editGoalForm = useForm({
    title: '', description: '', target_date: '', progress: 0, status: 'not_started',
});

const createTask = () => {
    taskForm.post(`/projects/${props.project.id}/tasks`, {
        onSuccess: () => { showTaskModal.value = false; taskForm.reset(); },
    });
};

const openEditTask = (task) => {
    editingTask.value = task;
    editTaskForm.title = task.title;
    editTaskForm.description = task.description || '';
    editTaskForm.priority = task.priority;
    editTaskForm.status = task.status;
    editTaskForm.due_date = task.due_date ? task.due_date.substring(0, 10) : '';
    editTaskForm.assigned_to = task.assigned_to ?? user.id;
    showEditTaskModal.value = true;
};

const updateTask = () => {
    editTaskForm.put(`/tasks/${editingTask.value.id}`, {
        onSuccess: () => { showEditTaskModal.value = false; editingTask.value = null; },
    });
};

const deleteTask = (taskId) => {
    if (confirm(t('common.confirm_delete'))) {
        router.delete(`/tasks/${taskId}`);
    }
};

const openTaskDetail = (task) => {
    detailTask.value = task;
    showTaskDetail.value = true;
};

const createGoal = () => {
    goalForm.post(`/projects/${props.project.id}/goals`, {
        onSuccess: () => { showGoalModal.value = false; goalForm.reset(); },
    });
};

const openEditGoal = (goal) => {
    editingGoal.value = goal;
    editGoalForm.title = goal.title;
    editGoalForm.description = goal.description || '';
    editGoalForm.target_date = goal.target_date ? goal.target_date.substring(0, 10) : '';
    editGoalForm.progress = goal.progress;
    editGoalForm.status = goal.status;
    showEditGoalModal.value = true;
};

const updateGoal = () => {
    editGoalForm.put(`/goals/${editingGoal.value.id}`, {
        onSuccess: () => { showEditGoalModal.value = false; editingGoal.value = null; },
    });
};

const updateGoalProgress = (goalId, progress) => {
    router.put(`/goals/${goalId}`, { progress: parseInt(progress) }, { preserveScroll: true });
};

const deleteGoal = (goalId) => {
    if (confirm(t('common.confirm_delete'))) {
        router.delete(`/goals/${goalId}`);
    }
};

const updateTaskStatus = (taskId, status) => {
    router.patch(`/tasks/${taskId}/status`, { status });
};

const isProjectAdmin = () => {
    if (props.project.owner_id === user.id) return true;
    const member = props.project.members?.find(m => m.id === user.id);
    return ['owner', 'boss', 'manager'].includes(member?.pivot?.role);
};

const assigneeOptions = computed(() => {
    if (!props.project.members) return [];
    if (isProjectAdmin()) return props.project.members;
    return props.project.members.filter(m => m.id === user.id);
});

const assignTaskToMe = (taskId) => {
    router.post(`/tasks/${taskId}/assign-to-me`, {}, { preserveScroll: true });
};

const deleteProject = () => {
    if (confirm(t('common.confirm_delete'))) {
        router.delete(`/projects/${props.project.id}`);
    }
};

// Drag & Drop
const onDragStart = (e, taskId) => {
    draggedTaskId.value = taskId;
    e.dataTransfer.effectAllowed = 'move';
};

const onDragOver = (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
};

const onDrop = (e, targetStatus) => {
    e.preventDefault();
    if (draggedTaskId.value) {
        updateTaskStatus(draggedTaskId.value, targetStatus);
        draggedTaskId.value = null;
    }
};

const priorityColors = {
    urgent: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    high: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    medium: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    low: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
};

const statusColors = {
    not_started: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    in_progress: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    achieved: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    missed: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
};

const memberForm = useForm({
    email: '', role: 'member',
});

const addMember = () => {
    memberForm.post(`/projects/${props.project.id}/members`, {
        onSuccess: () => { showMemberModal.value = false; memberForm.reset(); },
    });
};

const removeMember = (userId) => {
    if (confirm(t('projects.remove_member_confirm'))) {
        router.delete(`/projects/${props.project.id}/members/${userId}`);
    }
};

const updateMemberRole = (userId, role) => {
    router.put(`/projects/${props.project.id}/members/${userId}`, { role }, { preserveScroll: true });
};

const statusColumns = ['todo', 'in_progress', 'review', 'done'];
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <Link href="/projects" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: project.color }"></div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ project.name }}</h1>
            </div>
            <div class="flex gap-2">
                <Link :href="`/projects/${project.id}/edit`" class="btn-secondary text-sm">{{ t('common.edit') }}</Link>
                <button @click="deleteProject" class="btn-danger text-sm">{{ t('common.delete') }}</button>
            </div>
        </div>

        <p v-if="project.description" class="text-gray-500 dark:text-gray-400 mb-6">{{ project.description }}</p>

        <!-- Tabs -->
        <div class="flex gap-1 mb-6 border-b border-gray-200 dark:border-gray-700">
            <button v-for="tab in ['tasks', 'goals', 'notes', 'members']" :key="tab"
                    @click="activeTab = tab"
                    :class="['px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors',
                             activeTab === tab ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
                {{ t(`nav.${tab}`) }}
            </button>
        </div>

        <!-- Tasks Tab - Kanban with Drag & Drop -->
        <div v-if="activeTab === 'tasks'">
            <div class="flex justify-end mb-4">
                <button @click="showTaskModal = true" class="btn-primary text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ t('tasks.new') }}
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div v-for="col in statusColumns" :key="col"
                     class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 min-h-[200px]"
                     @dragover="onDragOver"
                     @drop="onDrop($event, col)">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        {{ t(`tasks.${col}`) }}
                        <span class="text-xs text-gray-400 ml-1">({{ (project.tasks || []).filter(t => t.status === col).length }})</span>
                    </h3>
                    <div class="space-y-3">
                        <div v-for="task in (project.tasks || []).filter(t => t.status === col)" :key="task.id"
                             class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-gray-700 cursor-grab active:cursor-grabbing"
                             draggable="true"
                             @dragstart="onDragStart($event, task.id)">
                            <div class="flex items-start justify-between mb-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer hover:text-primary-600"
                                   @click="openTaskDetail(task)">{{ task.title }}</p>
                                <div class="flex items-center gap-1 flex-shrink-0 ml-2">
                                    <button @click="openEditTask(task)" class="text-gray-400 hover:text-primary-500 p-0.5" :title="t('common.edit')">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="deleteTask(task.id)" class="text-gray-400 hover:text-red-500 p-0.5" :title="t('common.delete')">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span :class="[priorityColors[task.priority], 'text-xs px-2 py-0.5 rounded-full font-medium']">{{ t(`tasks.${task.priority}`) }}</span>
                                <div class="flex items-center gap-2">
                                    <button v-if="!task.assignee" @click="assignTaskToMe(task.id)" class="text-xs px-2 py-0.5 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 hover:bg-primary-200">
                                        {{ t('tasks.take_it') }}
                                    </button>
                                    <div v-else class="flex items-center gap-1">
                                        <div class="w-6 h-6 bg-primary-500 rounded-full flex items-center justify-center text-white text-xs">
                                            {{ task.assignee.name.charAt(0) }}
                                        </div>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ task.assignee.name }}</span>
                                    </div>
                                </div>
                            </div>
                            <p v-if="task.due_date" class="text-xs text-gray-400 mt-1">{{ new Date(task.due_date).toLocaleDateString() }}</p>
                            <!-- Subtask progress -->
                            <div v-if="task.subtasks?.length" class="mt-2 flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="bg-primary-500 h-1.5 rounded-full transition-all"
                                         :style="{ width: `${(task.subtasks.filter(s => s.status === 'done').length / task.subtasks.length * 100)}%` }"></div>
                                </div>
                                <span class="text-xs text-gray-400">{{ task.subtasks.filter(s => s.status === 'done').length }}/{{ task.subtasks.length }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Goals Tab with Edit/Delete/Progress -->
        <div v-if="activeTab === 'goals'">
            <div class="flex justify-end mb-4">
                <button @click="showGoalModal = true" class="btn-primary text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ t('goals.new') }}
                </button>
            </div>
            <div v-if="!(project.goals || []).length" class="card p-12 text-center">
                <p class="text-gray-500">{{ t('goals.no_goals') }}</p>
            </div>
            <div v-else class="space-y-4">
                <div v-for="goal in project.goals" :key="goal.id" class="card p-5">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ goal.title }}</h3>
                            <p v-if="goal.description" class="text-sm text-gray-500 mt-1">{{ goal.description }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                            <span :class="[statusColors[goal.status], 'text-xs px-2.5 py-1 rounded-full font-medium']">{{ t(`goals.${goal.status}`) }}</span>
                            <button @click="openEditGoal(goal)" class="text-gray-400 hover:text-primary-500 p-1" :title="t('common.edit')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button @click="deleteGoal(goal.id)" class="text-gray-400 hover:text-red-500 p-1" :title="t('common.delete')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-primary-500 h-2 rounded-full transition-all" :style="{ width: `${goal.progress}%` }"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-10 text-right">{{ goal.progress }}%</span>
                    </div>
                    <input type="range" min="0" max="100" step="5" :value="goal.progress"
                           @change="updateGoalProgress(goal.id, $event.target.value)"
                           class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary-500" />
                    <p v-if="goal.target_date" class="text-xs text-gray-400 mt-2">{{ t('goals.target_date') }}: {{ new Date(goal.target_date).toLocaleDateString() }}</p>
                </div>
            </div>
        </div>

        <!-- Notes Tab -->
        <div v-if="activeTab === 'notes'">
            <div class="space-y-4">
                <div v-for="note in project.notes" :key="note.id" class="card p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <span v-if="note.is_pinned" class="text-amber-500">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"/></svg>
                        </span>
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ note.title }}</h3>
                    </div>
                    <div v-if="note.body" class="text-sm text-gray-500 dark:text-gray-400 line-clamp-3 prose prose-sm" v-html="note.body"></div>
                </div>
            </div>
        </div>

        <!-- Members Tab -->
        <div v-if="activeTab === 'members'">
            <div class="flex justify-end mb-4">
                <button @click="showMemberModal = true" class="btn-primary text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ t('projects.add_member') }}
                </button>
            </div>
            <div v-if="!(project.members || []).length" class="card p-8 text-center">
                <p class="text-gray-500">{{ t('projects.no_members') }}</p>
            </div>
            <div v-else class="space-y-2">
                <div v-for="member in project.members" :key="member.id" class="card p-4 flex items-center gap-4">
                    <div class="w-10 h-10 bg-primary-500 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ member.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ member.name }}</p>
                        <p class="text-xs text-gray-500">{{ member.email }}</p>
                    </div>
                    <select
                        :value="member.pivot?.role || 'member'"
                        @change="updateMemberRole(member.id, $event.target.value)"
                        class="text-xs px-2 py-1 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                    >
                        <option value="boss">{{ t('projects.role_boss') }}</option>
                        <option value="manager">{{ t('projects.role_manager') }}</option>
                        <option value="member">{{ t('projects.role_member') }}</option>
                        <option value="viewer">{{ t('projects.role_viewer') }}</option>
                    </select>
                    <button @click="removeMember(member.id)" class="text-gray-400 hover:text-red-500 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Task Detail Slide-out -->
        <div v-if="showTaskDetail && detailTask" class="fixed inset-0 z-50 flex justify-end bg-black/50" @click.self="showTaskDetail = false">
            <div class="bg-white dark:bg-gray-800 w-full max-w-lg h-full shadow-xl overflow-y-auto p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ detailTask.title }}</h3>
                    <button @click="showTaskDetail = false" class="text-gray-400 hover:text-gray-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span :class="[priorityColors[detailTask.priority], 'text-xs px-2.5 py-1 rounded-full font-medium']">{{ t(`tasks.${detailTask.priority}`) }}</span>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-medium">{{ t(`tasks.${detailTask.status}`) }}</span>
                    </div>
                    <div v-if="detailTask.description" class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ detailTask.description }}</div>
                    <div v-else class="text-sm text-gray-400 italic">{{ t('common.no_description') }}</div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-3">
                        <div v-if="detailTask.due_date" class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-gray-600 dark:text-gray-400">{{ new Date(detailTask.due_date).toLocaleDateString() }}</span>
                        </div>
                        <div v-if="detailTask.assignee" class="flex items-center gap-2 text-sm">
                            <div class="w-6 h-6 bg-primary-500 rounded-full flex items-center justify-center text-white text-xs">{{ detailTask.assignee.name.charAt(0) }}</div>
                            <span class="text-gray-600 dark:text-gray-400">{{ detailTask.assignee.name }}</span>
                        </div>
                    </div>
                    <!-- Subtasks -->
                    <div v-if="detailTask.subtasks?.length" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ t('tasks.subtasks') }} ({{ detailTask.subtasks.filter(s => s.status === 'done').length }}/{{ detailTask.subtasks.length }})</h4>
                        <div class="space-y-2">
                            <div v-for="sub in detailTask.subtasks" :key="sub.id" class="flex items-center gap-3">
                                <input type="checkbox" :checked="sub.status === 'done'"
                                       @change="updateTaskStatus(sub.id, sub.status === 'done' ? 'todo' : 'done')"
                                       class="w-4 h-4 text-primary-500 rounded border-gray-300 focus:ring-primary-500" />
                                <span :class="['text-sm', sub.status === 'done' ? 'text-gray-400 line-through' : 'text-gray-700 dark:text-gray-300']">{{ sub.title }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex gap-2">
                        <button @click="openEditTask(detailTask); showTaskDetail = false" class="btn-primary text-sm flex-1">{{ t('common.edit') }}</button>
                        <button @click="deleteTask(detailTask.id); showTaskDetail = false" class="btn-danger text-sm">{{ t('common.delete') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Member Modal -->
        <div v-if="showMemberModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showMemberModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('projects.add_member') }}</h3>
                <form @submit.prevent="addMember" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('auth.email') }}</label>
                        <input v-model="memberForm.email" type="email" required class="input-field" placeholder="member@example.com" />
                        <p v-if="memberForm.errors.email" class="mt-1 text-sm text-red-500">{{ memberForm.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('projects.role') }}</label>
                        <select v-model="memberForm.role" class="input-field">
                            <option value="boss">{{ t('projects.role_boss') }}</option>
                            <option value="manager">{{ t('projects.role_manager') }}</option>
                            <option value="member">{{ t('projects.role_member') }}</option>
                            <option value="viewer">{{ t('projects.role_viewer') }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showMemberModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="memberForm.processing" class="btn-primary">{{ t('projects.add_member') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create Task Modal -->
        <div v-if="showTaskModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showTaskModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('tasks.new') }}</h3>
                <form @submit.prevent="createTask" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.task_title') }}</label>
                        <input v-model="taskForm.title" type="text" required class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.description') }}</label>
                        <textarea v-model="taskForm.description" rows="2" class="input-field"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.priority') }}</label>
                            <select v-model="taskForm.priority" class="input-field">
                                <option value="low">{{ t('tasks.low') }}</option>
                                <option value="medium">{{ t('tasks.medium') }}</option>
                                <option value="high">{{ t('tasks.high') }}</option>
                                <option value="urgent">{{ t('tasks.urgent') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.due_date') }}</label>
                            <input v-model="taskForm.due_date" type="date" class="input-field" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.assignee') }}</label>
                        <select v-model="taskForm.assigned_to" class="input-field">
                            <option :value="null" v-if="isProjectAdmin()">{{ t('tasks.unassigned') }}</option>
                            <option v-for="member in assigneeOptions" :key="member.id" :value="member.id">{{ member.name }}</option>
                        </select>
                        <p v-if="taskForm.errors.assigned_to" class="mt-1 text-sm text-red-500">{{ taskForm.errors.assigned_to }}</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showTaskModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="taskForm.processing" class="btn-primary">{{ t('common.create') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Task Modal -->
        <div v-if="showEditTaskModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showEditTaskModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('common.edit') }} {{ t('tasks.title') }}</h3>
                <form @submit.prevent="updateTask" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.task_title') }}</label>
                        <input v-model="editTaskForm.title" type="text" required class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.description') }}</label>
                        <textarea v-model="editTaskForm.description" rows="2" class="input-field"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.priority') }}</label>
                            <select v-model="editTaskForm.priority" class="input-field">
                                <option value="low">{{ t('tasks.low') }}</option>
                                <option value="medium">{{ t('tasks.medium') }}</option>
                                <option value="high">{{ t('tasks.high') }}</option>
                                <option value="urgent">{{ t('tasks.urgent') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.status') }}</label>
                            <select v-model="editTaskForm.status" class="input-field">
                                <option v-for="s in ['todo', 'in_progress', 'review', 'done', 'cancelled']" :key="s" :value="s">{{ t(`tasks.${s}`) }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.due_date') }}</label>
                        <input v-model="editTaskForm.due_date" type="date" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.assignee') }}</label>
                        <select v-model="editTaskForm.assigned_to" class="input-field">
                            <option :value="null" v-if="isProjectAdmin()">{{ t('tasks.unassigned') }}</option>
                            <option v-for="member in assigneeOptions" :key="member.id" :value="member.id">{{ member.name }}</option>
                        </select>
                        <p v-if="editTaskForm.errors.assigned_to" class="mt-1 text-sm text-red-500">{{ editTaskForm.errors.assigned_to }}</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showEditTaskModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="editTaskForm.processing" class="btn-primary">{{ t('common.save') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create Goal Modal -->
        <div v-if="showGoalModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showGoalModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('goals.new') }}</h3>
                <form @submit.prevent="createGoal" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('goals.goal_title') }}</label>
                        <input v-model="goalForm.title" type="text" required class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.description') }}</label>
                        <textarea v-model="goalForm.description" rows="2" class="input-field"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('goals.target_date') }}</label>
                        <input v-model="goalForm.target_date" type="date" class="input-field" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showGoalModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="goalForm.processing" class="btn-primary">{{ t('common.create') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Goal Modal -->
        <div v-if="showEditGoalModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showEditGoalModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('common.edit') }} {{ t('goals.title') }}</h3>
                <form @submit.prevent="updateGoal" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('goals.goal_title') }}</label>
                        <input v-model="editGoalForm.title" type="text" required class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.description') }}</label>
                        <textarea v-model="editGoalForm.description" rows="2" class="input-field"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('goals.target_date') }}</label>
                        <input v-model="editGoalForm.target_date" type="date" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('goals.progress') }} ({{ editGoalForm.progress }}%)</label>
                        <input type="range" v-model.number="editGoalForm.progress" min="0" max="100" step="5"
                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('tasks.status') }}</label>
                        <select v-model="editGoalForm.status" class="input-field">
                            <option v-for="s in ['not_started', 'in_progress', 'achieved', 'missed']" :key="s" :value="s">{{ t(`goals.${s}`) }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showEditGoalModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="editGoalForm.processing" class="btn-primary">{{ t('common.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
