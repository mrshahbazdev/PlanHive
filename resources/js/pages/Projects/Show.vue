<script setup>
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({ project: Object });

const showTaskModal = ref(false);
const showGoalModal = ref(false);
const showMemberModal = ref(false);
const activeTab = ref('tasks');

const taskForm = useForm({
    title: '', description: '', priority: 'medium', status: 'todo', due_date: '', assigned_to: null,
});

const goalForm = useForm({
    title: '', description: '', target_date: '',
});

const createTask = () => {
    taskForm.post(`/projects/${props.project.id}/tasks`, {
        onSuccess: () => { showTaskModal.value = false; taskForm.reset(); },
    });
};

const createGoal = () => {
    goalForm.post(`/projects/${props.project.id}/goals`, {
        onSuccess: () => { showGoalModal.value = false; goalForm.reset(); },
    });
};

const updateTaskStatus = (taskId, status) => {
    router.patch(`/tasks/${taskId}/status`, { status });
};

const deleteProject = () => {
    if (confirm(t('common.confirm_delete'))) {
        router.delete(`/projects/${props.project.id}`);
    }
};

const priorityColors = {
    urgent: 'bg-red-100 text-red-700', high: 'bg-orange-100 text-orange-700',
    medium: 'bg-blue-100 text-blue-700', low: 'bg-gray-100 text-gray-700',
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
    if (confirm('Remove this member?')) {
        router.delete(`/projects/${props.project.id}/members/${userId}`);
    }
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

        <!-- Tasks Tab - Kanban -->
        <div v-if="activeTab === 'tasks'">
            <div class="flex justify-end mb-4">
                <button @click="showTaskModal = true" class="btn-primary text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ t('tasks.new') }}
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div v-for="col in statusColumns" :key="col" class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ t(`tasks.${col}`) }}</h3>
                    <div class="space-y-3">
                        <div v-for="task in (project.tasks || []).filter(t => t.status === col)" :key="task.id"
                             class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-gray-700">
                            <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ task.title }}</p>
                            <div class="flex items-center justify-between">
                                <span :class="[priorityColors[task.priority], 'text-xs px-2 py-0.5 rounded-full font-medium']">{{ t(`tasks.${task.priority}`) }}</span>
                                <div v-if="task.assignee" class="w-6 h-6 bg-primary-500 rounded-full flex items-center justify-center text-white text-xs">
                                    {{ task.assignee.name.charAt(0) }}
                                </div>
                            </div>
                            <div v-if="col !== 'done'" class="mt-2 flex gap-1">
                                <button v-for="s in statusColumns.filter(s => s !== col)" :key="s"
                                        @click="updateTaskStatus(task.id, s)"
                                        class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600">
                                    &rarr; {{ t(`tasks.${s}`) }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Goals Tab -->
        <div v-if="activeTab === 'goals'">
            <div class="flex justify-end mb-4">
                <button @click="showGoalModal = true" class="btn-primary text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ t('goals.new') }}
                </button>
            </div>
            <div class="space-y-4">
                <div v-for="goal in project.goals" :key="goal.id" class="card p-5">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ goal.title }}</h3>
                        <span class="text-sm text-gray-500">{{ goal.progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-primary-500 h-2 rounded-full transition-all" :style="{ width: `${goal.progress}%` }"></div>
                    </div>
                    <p v-if="goal.description" class="text-sm text-gray-500 mt-2">{{ goal.description }}</p>
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
                    Add Member
                </button>
            </div>
            <div v-if="!(project.members || []).length" class="card p-8 text-center">
                <p class="text-gray-500">No members yet. Invite someone to collaborate!</p>
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
                    <span class="text-xs px-2 py-1 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 font-medium">
                        {{ member.pivot?.role || 'member' }}
                    </span>
                    <button @click="removeMember(member.id)" class="text-gray-400 hover:text-red-500 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Add Member Modal -->
        <div v-if="showMemberModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showMemberModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Member</h3>
                <form @submit.prevent="addMember" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input v-model="memberForm.email" type="email" required class="input-field" placeholder="member@example.com" />
                        <p v-if="memberForm.errors.email" class="mt-1 text-sm text-red-500">{{ memberForm.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                        <select v-model="memberForm.role" class="input-field">
                            <option value="boss">Boss</option>
                            <option value="manager">Manager</option>
                            <option value="member">Member</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showMemberModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="memberForm.processing" class="btn-primary">Add Member</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Task Modal -->
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
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showTaskModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="taskForm.processing" class="btn-primary">{{ t('common.create') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Goal Modal -->
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
    </div>
</template>
