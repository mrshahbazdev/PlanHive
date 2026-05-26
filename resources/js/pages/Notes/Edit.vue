<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import TiptapEditor from '../../components/TiptapEditor.vue';

const { t } = useI18n();
const props = defineProps({ note: Object, projects: Array });

const form = useForm({
    title: props.note.title,
    body: props.note.body || '',
    project_id: props.note.project_id,
    is_pinned: props.note.is_pinned,
});

const submit = () => {
    form.put(`/notes/${props.note.id}`);
};
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <Link :href="`/notes/${note.id}`" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </Link>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('common.edit') }}: {{ note.title }}</h1>
        </div>

        <div class="card p-8">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('notes.note_title') }} *</label>
                    <input v-model="form.title" type="text" required class="input-field" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('notes.content') }}</label>
                    <TiptapEditor v-model="form.body" :placeholder="t('notes.content')" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('nav.projects') }}</label>
                    <select v-model="form.project_id" class="input-field">
                        <option :value="null">-- None --</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input v-model="form.is_pinned" type="checkbox" id="pinned" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                    <label for="pinned" class="text-sm text-gray-700 dark:text-gray-300">{{ t('notes.pinned') }}</label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <Link :href="`/notes/${note.id}`" class="btn-secondary">{{ t('common.cancel') }}</Link>
                    <button type="submit" :disabled="form.processing" class="btn-primary">{{ t('common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</template>
