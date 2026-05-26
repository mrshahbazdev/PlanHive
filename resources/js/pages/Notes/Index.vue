<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
defineProps({ notes: Object });
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('notes.title') }}</h1>
            <Link href="/notes/create" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ t('notes.new') }}
            </Link>
        </div>

        <div v-if="!notes?.data?.length" class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-gray-500 dark:text-gray-400">{{ t('notes.no_notes') }}</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <Link v-for="note in notes.data" :key="note.id" :href="`/notes/${note.id}`"
                  class="card p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center gap-2 mb-2">
                    <svg v-if="note.is_pinned" class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"/></svg>
                    <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 truncate">{{ note.title }}</h3>
                </div>
                <div v-if="note.body" class="text-sm text-gray-500 dark:text-gray-400 line-clamp-3" v-html="note.body"></div>
                <div class="flex items-center gap-2 mt-3 text-xs text-gray-400">
                    <span v-if="note.project" class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: note.project.color }"></span>
                        {{ note.project.name }}
                    </span>
                    <span>{{ new Date(note.updated_at).toLocaleDateString() }}</span>
                </div>
            </Link>
        </div>
    </div>
</template>
