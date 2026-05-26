<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '../../layouts/AppLayout.vue';

const { t } = useI18n();
const page = usePage();

const props = defineProps({
    tokens: Array,
});

const showCreate = ref(false);
const newToken = computed(() => page.props.flash?.token || null);
const form = ref({ name: '', abilities: ['projects:read', 'tasks:read', 'contacts:read'] });

const availableAbilities = [
    { value: 'projects:read', label: 'Read Projects' },
    { value: 'projects:write', label: 'Write Projects' },
    { value: 'tasks:read', label: 'Read Tasks' },
    { value: 'tasks:write', label: 'Write Tasks' },
    { value: 'contacts:read', label: 'Read Contacts' },
    { value: 'contacts:write', label: 'Write Contacts' },
];

const toggleAbility = (ability) => {
    const idx = form.value.abilities.indexOf(ability);
    if (idx > -1) form.value.abilities.splice(idx, 1);
    else form.value.abilities.push(ability);
};

const createToken = () => {
    router.post('/api-tokens', form.value, {
        onSuccess: () => {
            form.value = { name: '', abilities: ['projects:read', 'tasks:read', 'contacts:read'] };
            showCreate.value = false;
        },
    });
};

const revokeToken = (id) => {
    if (confirm('Revoke this token? This action cannot be undone.')) {
        router.delete(`/api-tokens/${id}`);
    }
};

const apiDocs = [
    { method: 'GET', path: '/api/v1/projects', desc: 'List your projects' },
    { method: 'POST', path: '/api/v1/projects', desc: 'Create a project' },
    { method: 'GET', path: '/api/v1/projects/{id}', desc: 'Get project details' },
    { method: 'PUT', path: '/api/v1/projects/{id}', desc: 'Update a project' },
    { method: 'DELETE', path: '/api/v1/projects/{id}', desc: 'Delete a project' },
    { method: 'GET', path: '/api/v1/tasks', desc: 'List your tasks (supports ?status, ?priority, ?project_id filters)' },
    { method: 'POST', path: '/api/v1/tasks', desc: 'Create a task' },
    { method: 'GET', path: '/api/v1/tasks/{id}', desc: 'Get task details' },
    { method: 'PUT', path: '/api/v1/tasks/{id}', desc: 'Update a task' },
    { method: 'DELETE', path: '/api/v1/tasks/{id}', desc: 'Delete a task' },
    { method: 'GET', path: '/api/v1/contacts', desc: 'List your contacts (supports ?search filter)' },
    { method: 'POST', path: '/api/v1/contacts', desc: 'Create a contact' },
    { method: 'GET', path: '/api/v1/contacts/{id}', desc: 'Get contact details' },
    { method: 'PUT', path: '/api/v1/contacts/{id}', desc: 'Update a contact' },
    { method: 'DELETE', path: '/api/v1/contacts/{id}', desc: 'Delete a contact' },
];

const methodColor = (m) => {
    const map = { GET: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400', POST: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400', PUT: 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400', DELETE: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' };
    return map[m] || '';
};
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-5xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">API & Tokens</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Manage API tokens and view endpoint documentation.</p>

            <!-- New Token Flash -->
            <div v-if="newToken" class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                <p class="text-sm font-medium text-green-800 dark:text-green-400 mb-2">Token created! Copy it now — it won't be shown again.</p>
                <code class="block bg-white dark:bg-gray-800 p-3 rounded-lg text-sm font-mono text-gray-900 dark:text-gray-100 break-all">{{ newToken }}</code>
            </div>

            <!-- Token Management -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 mb-8">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">API Tokens</h3>
                    <button @click="showCreate = !showCreate" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm hover:bg-primary-700">
                        {{ showCreate ? 'Cancel' : 'Create Token' }}
                    </button>
                </div>

                <!-- Create Form -->
                <div v-if="showCreate" class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Token Name</label>
                            <input v-model="form.name" type="text" placeholder="e.g. Mobile App" class="w-full max-w-md px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Permissions</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="ab in availableAbilities"
                                    :key="ab.value"
                                    @click="toggleAbility(ab.value)"
                                    :class="['px-3 py-1.5 rounded-lg text-sm border', form.abilities.includes(ab.value) ? 'bg-primary-600 text-white border-primary-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600']"
                                >{{ ab.label }}</button>
                            </div>
                        </div>
                        <button @click="createToken" :disabled="!form.name" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm hover:bg-primary-700 disabled:opacity-50">Create</button>
                    </div>
                </div>

                <!-- Token List -->
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div v-for="token in tokens" :key="token.id" class="p-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ token.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Created {{ new Date(token.created_at).toLocaleDateString() }}
                                <span v-if="token.last_used_at"> · Last used {{ new Date(token.last_used_at).toLocaleDateString() }}</span>
                            </p>
                        </div>
                        <button @click="revokeToken(token.id)" class="text-xs px-3 py-1.5 rounded bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 hover:bg-red-200">Revoke</button>
                    </div>
                    <div v-if="!tokens.length" class="p-8 text-center text-gray-500 dark:text-gray-400">No API tokens created yet</div>
                </div>
            </div>

            <!-- API Documentation -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">API Endpoints</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Base URL: <code class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-xs">{your-domain}</code> · Auth: <code class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded text-xs">Authorization: Bearer {token}</code></p>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div v-for="endpoint in apiDocs" :key="endpoint.path + endpoint.method" class="p-4 flex items-center gap-4">
                        <span :class="['px-2 py-1 text-xs font-mono font-bold rounded w-16 text-center', methodColor(endpoint.method)]">{{ endpoint.method }}</span>
                        <code class="text-sm font-mono text-gray-900 dark:text-gray-100 flex-1">{{ endpoint.path }}</code>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ endpoint.desc }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
