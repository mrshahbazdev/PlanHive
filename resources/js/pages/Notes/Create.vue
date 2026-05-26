<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const form = useForm({
    title: '',
    body: '',
    project_id: null,
    is_pinned: false,
});

const submit = () => {
    form.post('/notes');
};
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <Link href="/notes" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </Link>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('notes.new') }}</h1>
        </div>

        <div class="card p-8">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('notes.note_title') }} *</label>
                    <input v-model="form.title" type="text" required class="input-field" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('notes.content') }}</label>
                    <textarea v-model="form.body" rows="10" class="input-field" :placeholder="t('notes.content')"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input v-model="form.is_pinned" type="checkbox" id="pinned" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                    <label for="pinned" class="text-sm text-gray-700 dark:text-gray-300">{{ t('notes.pinned') }}</label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <Link href="/notes" class="btn-secondary">{{ t('common.cancel') }}</Link>
                    <button type="submit" :disabled="form.processing" class="btn-primary">{{ t('common.create') }}</button>
                </div>
            </form>
        </div>
    </div>
</template>
