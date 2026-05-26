<script setup>
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({ note: Object });

const deleteNote = () => {
    if (confirm(t('common.confirm_delete'))) {
        router.delete(`/notes/${props.note.id}`);
    }
};
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <Link href="/notes" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ note.title }}</h1>
            </div>
            <button @click="deleteNote" class="btn-danger text-sm">{{ t('common.delete') }}</button>
        </div>

        <div class="card p-8">
            <div v-if="note.project" class="flex items-center gap-2 mb-4">
                <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: note.project.color }"></span>
                <span class="text-sm text-gray-500">{{ note.project.name }}</span>
            </div>
            <div class="prose prose-sm dark:prose-invert max-w-none" v-html="note.body || '<p class=\'text-gray-400\'>No content</p>'"></div>
        </div>
    </div>
</template>
