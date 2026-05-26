<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import axios from 'axios';

const { t } = useI18n();

const props = defineProps({
    projects: Array,
    upcomingTasks: Array,
    calendarEvents: Array,
    stats: Object,
});

const showEventModal = ref(false);
const newEvent = ref({ title: '', start_at: '', end_at: '', project_id: null, all_day: false });

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    events: props.calendarEvents,
    editable: true,
    selectable: true,
    dayMaxEvents: 3,
    height: 'auto',
    dateClick(info) {
        newEvent.value.start_at = info.dateStr;
        newEvent.value.end_at = info.dateStr;
        newEvent.value.all_day = true;
        showEventModal.value = true;
    },
    eventDrop(info) {
        const eventId = info.event.id;
        if (String(eventId).startsWith('task-')) return;
        axios.put(`/api/calendar-events/${eventId}`, {
            start_at: info.event.start.toISOString(),
            end_at: (info.event.end || info.event.start).toISOString(),
        });
    },
}));

const createEvent = async () => {
    if (!newEvent.value.title) return;
    await axios.post('/api/calendar-events', {
        ...newEvent.value,
        end_at: newEvent.value.end_at || newEvent.value.start_at,
    });
    showEventModal.value = false;
    newEvent.value = { title: '', start_at: '', end_at: '', project_id: null, all_day: false };
    window.location.reload();
};

const statCards = computed(() => [
    { label: t('dashboard.total_projects'), value: props.stats.total_projects, color: 'bg-primary-500', icon: 'folder' },
    { label: t('dashboard.active_tasks'), value: props.stats.active_tasks, color: 'bg-amber-500', icon: 'tasks' },
    { label: t('dashboard.due_today'), value: props.stats.due_today, color: 'bg-blue-500', icon: 'clock' },
    { label: t('dashboard.overdue'), value: props.stats.overdue, color: 'bg-red-500', icon: 'alert' },
]);

const getPriorityColor = (priority) => {
    const colors = { urgent: 'text-red-600 bg-red-50', high: 'text-orange-600 bg-orange-50', medium: 'text-blue-600 bg-blue-50', low: 'text-gray-600 bg-gray-50' };
    return colors[priority] || colors.medium;
};
</script>

<template>
    <div>
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('dashboard.welcome') }}, {{ $page.props.auth.user?.name?.split(' ')[0] }}</h1>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div v-for="stat in statCards" :key="stat.label" class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ stat.label }}</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stat.value }}</p>
                    </div>
                    <div :class="[stat.color, 'w-12 h-12 rounded-xl flex items-center justify-center']">
                        <svg v-if="stat.icon === 'folder'" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <svg v-if="stat.icon === 'tasks'" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <svg v-if="stat.icon === 'clock'" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <svg v-if="stat.icon === 'alert'" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar + Sidebar -->
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            <!-- Calendar -->
            <div class="xl:col-span-3 card p-6">
                <FullCalendar :options="calendarOptions" />
            </div>

            <!-- Right Sidebar -->
            <div class="space-y-6">
                <!-- Today's Agenda -->
                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ t('dashboard.today_agenda') }}</h3>
                    <div v-if="upcomingTasks.length === 0" class="text-sm text-gray-500 dark:text-gray-400 py-4 text-center">
                        {{ t('dashboard.no_events') }}
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="task in upcomingTasks.slice(0, 5)" :key="task.id" class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: task.project?.color || '#14b8a6' }"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ task.title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ task.project?.name }}</p>
                            </div>
                            <span :class="[getPriorityColor(task.priority), 'text-xs px-2 py-0.5 rounded-full font-medium']">
                                {{ t(`tasks.${task.priority}`) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Projects -->
                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ t('nav.projects') }}</h3>
                    <div v-if="projects.length === 0" class="text-sm text-gray-500 py-4 text-center">
                        {{ t('projects.no_projects') }}
                    </div>
                    <div v-else class="space-y-2">
                        <a v-for="project in projects.slice(0, 8)" :key="project.id" :href="`/projects/${project.id}`"
                           class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: project.color }"></div>
                            <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ project.name }}</span>
                            <span class="text-xs text-gray-400 ml-auto">{{ project.tasks_count }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Event Modal -->
        <div v-if="showEventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showEventModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ t('calendar.new_event') }}</h3>
                <form @submit.prevent="createEvent" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('calendar.event_title') }}</label>
                        <input v-model="newEvent.title" type="text" required class="input-field" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('calendar.start') }}</label>
                            <input v-model="newEvent.start_at" type="datetime-local" class="input-field" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('calendar.end') }}</label>
                            <input v-model="newEvent.end_at" type="datetime-local" class="input-field" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('nav.projects') }}</label>
                        <select v-model="newEvent.project_id" class="input-field">
                            <option :value="null">-- None --</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showEventModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" class="btn-primary">{{ t('common.create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
