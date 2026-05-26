<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({
    integrations: Object,
    microsoftConfigured: Boolean,
});

const teamsForm = useForm({ webhook_url: '' });
const showTeamsModal = ref(false);

const connectTeams = () => {
    teamsForm.post('/integrations/teams', {
        onSuccess: () => { showTeamsModal.value = false; teamsForm.reset(); },
    });
};

const disconnect = (provider) => {
    if (confirm(`Disconnect ${provider}?`)) {
        router.delete(`/integrations/${provider}`);
    }
};

const syncCalendar = () => {
    router.post('/integrations/outlook/sync');
};

const testTeams = () => {
    router.post('/integrations/teams/test');
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('integrations.title') }}</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Outlook Calendar -->
            <div class="card p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7.88 12.04q0 .45-.11.87-.1.41-.33.74-.22.33-.58.52-.37.2-.87.2t-.85-.2q-.35-.21-.57-.55-.22-.33-.33-.75-.1-.42-.1-.86t.1-.87q.1-.43.34-.76.22-.34.59-.54.36-.2.87-.2t.86.2q.35.21.57.55.22.34.33.76.1.43.1.87zm-1.7 0q0-.28-.05-.52-.05-.25-.15-.45-.1-.2-.26-.31-.17-.12-.4-.12-.24 0-.4.12-.17.11-.28.31-.1.2-.15.45-.05.24-.05.52t.05.52q.05.25.15.44.1.2.26.31.16.12.4.12.23 0 .4-.12.16-.11.26-.31.1-.19.15-.44.05-.24.05-.52zM24 12v9.38q0 .46-.33.8-.33.32-.8.32H7.13q-.46 0-.8-.33-.32-.33-.32-.8V18H1q-.41 0-.7-.3-.3-.29-.3-.7V7q0-.41.3-.7Q.58 6 1 6h6.01V2.62q0-.46.33-.8.33-.32.8-.32h14.86q.46 0 .8.33.32.33.32.8zM7.01 11.24H5.7l.59-.59-.37-.54-1.18.97 1.29 1.05.33-.54-.55-.48h1.19zm1-.7l.97-1.17-.58-.33-.48.55V8.4H6.97v1.19l-.58-.55-.33.37 1.05 1.29 1.17-.97-.28-.18zm-.7 1.45H6.12v1.04l-.63-.52-.35.43.98 1.03 1.11-.9-.37-.46-.55.42zm-2.78 1.45l-.97 1.18.58.33.48-.55v1.19h.97v-1.2l.58.56.33-.37-1.05-1.29-1.17.98.24.17zM21 20V7H8v5h4.99q.42 0 .71.3.3.29.3.7v7zm-2.5-3.25q0-.46-.14-.84-.15-.39-.39-.65-.24-.27-.59-.42-.34-.15-.73-.15-.39 0-.73.15-.35.15-.59.42-.24.26-.38.65-.14.38-.14.84t.14.85q.14.38.38.65.24.27.59.42.34.15.73.15.39 0 .73-.15.35-.15.59-.42.24-.27.39-.65.14-.39.14-.85zm-1.54 0q0 .26-.05.48-.05.22-.17.39-.12.17-.3.26-.17.1-.42.1-.25 0-.43-.1-.17-.09-.3-.26-.12-.17-.17-.39-.05-.22-.05-.48 0-.25.05-.47.05-.22.17-.39.13-.17.3-.27.18-.1.43-.1.25 0 .42.1.18.1.3.27.12.17.17.39.05.22.05.47z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Microsoft Outlook</h3>
                        <p class="text-sm text-gray-500">{{ t('integrations.outlook_desc') }}</p>
                    </div>
                </div>

                <div v-if="integrations?.outlook" class="space-y-3">
                    <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Connected{{ integrations.outlook.settings?.email ? ` — ${integrations.outlook.settings.email}` : '' }}
                    </div>
                    <div class="flex gap-2">
                        <button @click="syncCalendar" class="btn-primary text-sm">Sync Calendar</button>
                        <button @click="disconnect('outlook')" class="btn-danger text-sm">Disconnect</button>
                    </div>
                </div>
                <div v-else>
                    <a v-if="microsoftConfigured" href="/integrations/outlook/connect" class="btn-primary text-sm">
                        Connect Outlook
                    </a>
                    <p v-else class="text-sm text-amber-600 dark:text-amber-400">
                        {{ t('integrations.config_required') }}
                    </p>
                </div>
            </div>

            <!-- Microsoft Teams -->
            <div class="card p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-purple-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19.2 6.4h-2.4V4.8c0-1.32-1.08-2.4-2.4-2.4H9.6c-1.32 0-2.4 1.08-2.4 2.4v1.6H4.8C3.48 6.4 2.4 7.48 2.4 8.8v9.6c0 1.32 1.08 2.4 2.4 2.4h14.4c1.32 0 2.4-1.08 2.4-2.4V8.8c0-1.32-1.08-2.4-2.4-2.4zM9.6 4.8h4.8v1.6H9.6V4.8zm4.8 10.8H9.6v-1.2h4.8v1.2zm2.4-3.6H7.2V10.8h9.6V12z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Microsoft Teams</h3>
                        <p class="text-sm text-gray-500">{{ t('integrations.teams_desc') }}</p>
                    </div>
                </div>

                <div v-if="integrations?.teams" class="space-y-3">
                    <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Webhook connected
                    </div>
                    <div class="flex gap-2">
                        <button @click="testTeams" class="btn-primary text-sm">Send Test</button>
                        <button @click="disconnect('teams')" class="btn-danger text-sm">Disconnect</button>
                    </div>
                </div>
                <div v-else>
                    <button @click="showTeamsModal = true" class="btn-primary text-sm">
                        Connect Teams
                    </button>
                </div>
            </div>
        </div>

        <!-- Teams Webhook Modal -->
        <div v-if="showTeamsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showTeamsModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Connect Microsoft Teams</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Paste your Teams Incoming Webhook URL. You can create one in Teams → Channel → Connectors → Incoming Webhook.
                </p>
                <form @submit.prevent="connectTeams" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Webhook URL</label>
                        <input v-model="teamsForm.webhook_url" type="url" required class="input-field" placeholder="https://outlook.office.com/webhook/..." />
                        <p v-if="teamsForm.errors.webhook_url" class="mt-1 text-sm text-red-500">{{ teamsForm.errors.webhook_url }}</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showTeamsModal = false" class="btn-secondary">Cancel</button>
                        <button type="submit" :disabled="teamsForm.processing" class="btn-primary">Connect</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
