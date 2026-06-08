<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({ notes: Object, filters: Object });

const search = ref(props.filters?.search || '');

const doSearch = () => {
    router.get('/notes', { search: search.value }, { preserveState: true });
};

const deleteNote = (id, e) => {
    e.preventDefault();
    e.stopPropagation();
    if (confirm(t('common.confirm_delete'))) {
        router.delete(`/notes/${id}`);
    }
};
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

        <!-- Search -->
        <div class="mb-6">
            <form @submit.prevent="doSearch" class="relative max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input v-model="search" type="text" :placeholder="t('common.search')" class="input-field pl-10" @keyup.enter="doSearch" />
            </form>
        </div>

        <div v-if="!notes?.data?.length" class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-gray-500 dark:text-gray-400">{{ t('notes.no_notes') }}</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <Link v-for="note in notes.data" :key="note.id" :href="`/notes/${note.id}`"
                  class="card p-5 hover:shadow-md transition-shadow group relative">
                <button @click="deleteNote(note.id, $event)" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
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
