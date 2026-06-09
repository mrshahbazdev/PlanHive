<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({ contacts: Object, filters: Object });

const showModal = ref(false);
const editingContact = ref(null);
const search = ref(props.filters?.search || '');

const form = useForm({
    first_name: '', last_name: '', email: '', phone: '',
    company: '', job_title: '', address: '', website: '',
    notes: '', project_id: null, source: 'manual', tags: [],
});

const newTag = ref('');
const addTag = () => {
    if (newTag.value && !form.tags.includes(newTag.value)) {
        form.tags.push(newTag.value);
        newTag.value = '';
    }
};
const removeTag = (idx) => form.tags.splice(idx, 1);

const openCreate = () => {
    editingContact.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (contact) => {
    editingContact.value = contact;
    form.first_name = contact.first_name;
    form.last_name = contact.last_name || '';
    form.email = contact.email || '';
    form.phone = contact.phone || '';
    form.company = contact.company || '';
    form.job_title = contact.job_title || '';
    form.address = contact.address || '';
    form.website = contact.website || '';
    form.notes = contact.notes || '';
    form.project_id = contact.project_id;
    form.tags = contact.tags?.map(t => t.tag) || [];
    showModal.value = true;
};

const submitForm = () => {
    if (editingContact.value) {
        form.put(`/contacts/${editingContact.value.id}`, {
            onSuccess: () => { showModal.value = false; editingContact.value = null; form.reset(); },
        });
    } else {
        form.post('/contacts', {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const doSearch = () => {
    router.get('/contacts', { search: search.value }, { preserveState: true });
};

const deleteContact = (id) => {
    if (confirm(t('common.confirm_delete'))) {
        router.delete(`/contacts/${id}`);
    }
};

const exportCSV = () => {
    const headers = [t('contacts.first_name'), t('contacts.last_name'), t('contacts.email'), t('contacts.phone'), t('contacts.company'), t('contacts.job_title')];
    const rows = (props.contacts?.data || []).map(c => [
        c.first_name, c.last_name || '', c.email || '', c.phone || '',
        c.company || '', c.job_title || '',
    ]);
    const csv = [headers, ...rows].map(r => r.map(c => `"${(c || '').replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'contacts.csv'; a.click();
    URL.revokeObjectURL(url);
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('contacts.title') }}</h1>
            <div class="flex items-center gap-2">
                <button @click="exportCSV" class="btn-secondary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    CSV
                </button>
                <button @click="openCreate" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ t('contacts.new') }}
                </button>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-6">
            <form @submit.prevent="doSearch" class="relative max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input v-model="search" type="text" :placeholder="t('common.search')" class="input-field pl-10" @keyup.enter="doSearch" />
            </form>
        </div>

        <div v-if="!contacts?.data?.length" class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-gray-500 dark:text-gray-400">{{ t('contacts.no_contacts') }}</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="contact in contacts.data" :key="contact.id" class="card p-5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-primary-700 dark:text-primary-400 font-semibold">
                            {{ contact.first_name.charAt(0) }}{{ contact.last_name?.charAt(0) || '' }}
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900 dark:text-white">{{ contact.first_name }} {{ contact.last_name }}</h3>
                            <p v-if="contact.company" class="text-xs text-gray-500">{{ contact.job_title ? `${contact.job_title} @ ` : '' }}{{ contact.company }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="openEdit(contact)" class="text-gray-400 hover:text-primary-500 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button @click="deleteContact(contact.id)" class="text-gray-400 hover:text-red-500 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
                <div class="mt-3 space-y-1 text-sm text-gray-500">
                    <p v-if="contact.email" class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ contact.email }}
                    </p>
                    <p v-if="contact.phone" class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ contact.phone }}
                    </p>
                </div>
                <div v-if="contact.tags?.length" class="flex flex-wrap gap-1 mt-3">
                    <span v-for="tag in contact.tags" :key="tag.id" class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full">{{ tag.tag }}</span>
                </div>
            </div>
        </div>

        <!-- Create / Edit Contact Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 overflow-y-auto py-8" @click.self="showModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 mx-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ editingContact ? t('common.edit') : t('contacts.new') }}</h3>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.first_name') }} *</label>
                            <input v-model="form.first_name" type="text" required class="input-field" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.last_name') }}</label>
                            <input v-model="form.last_name" type="text" class="input-field" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.email') }}</label>
                            <input v-model="form.email" type="email" class="input-field" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.phone') }}</label>
                            <input v-model="form.phone" type="text" class="input-field" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.company') }}</label>
                            <input v-model="form.company" type="text" class="input-field" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.job_title') }}</label>
                            <input v-model="form.job_title" type="text" class="input-field" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.tags') }}</label>
                        <div class="flex gap-2">
                            <input v-model="newTag" type="text" class="input-field flex-1" @keyup.enter.prevent="addTag" :placeholder="t('contacts.add_tag')" />
                            <button type="button" @click="addTag" class="btn-secondary text-sm">+</button>
                        </div>
                        <div class="flex flex-wrap gap-1 mt-2">
                            <span v-for="(tag, idx) in form.tags" :key="idx" class="text-xs bg-primary-100 text-primary-700 px-2 py-1 rounded-full flex items-center gap-1">
                                {{ tag }}
                                <button type="button" @click="removeTag(idx)" class="hover:text-red-500">&times;</button>
                            </span>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="btn-secondary">{{ t('common.cancel') }}</button>
                        <button type="submit" :disabled="form.processing" class="btn-primary">{{ editingContact ? t('common.save') : t('common.create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
