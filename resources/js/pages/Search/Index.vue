<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({
    results: Array,
    query: String,
    type: String,
});

const searchQuery = ref(props.query || '');
const searchType = ref(props.type || 'all');
let debounceTimer = null;

const types = ['all', 'projects', 'tasks', 'notes', 'contacts', 'goals'];

const doSearch = () => {
    if (searchQuery.value.length < 2) return;
    router.get('/search', { q: searchQuery.value, type: searchType.value }, { preserveState: true, replace: true });
};

watch(searchQuery, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(doSearch, 300);
});

watch(searchType, doSearch);

const typeIcons = {
    project: '📁', task: '✓', note: '📝', contact: '👤', goal: '🎯',
};

const typeColors = {
    project: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    task: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    note: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    contact: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    goal: 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400',
};
</script>

<template>
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ t('search.title') }}</h1>

        <!-- Search Bar -->
        <div class="card p-4 mb-6">
            <div class="flex gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="input-field pl-10 py-3 text-base"
                        :placeholder="t('search.placeholder')"
                        autofocus
                    />
                </div>
            </div>
            <div class="flex gap-2 mt-3">
                <button v-for="tp in types" :key="tp"
                        @click="searchType = tp"
                        :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-colors capitalize',
                                 searchType === tp ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600']">
                    {{ tp }}
                </button>
            </div>
        </div>

        <!-- Results -->
        <div v-if="!results?.length && query" class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <p class="text-gray-500">{{ t('common.no_results') }}</p>
        </div>

        <div v-else-if="results?.length" class="space-y-2">
            <Link v-for="item in results" :key="`${item.type}-${item.id}`" :href="item.url"
                  class="card p-4 flex items-center gap-4 hover:shadow-md transition-shadow block">
                <div :class="['w-10 h-10 rounded-lg flex items-center justify-center text-lg', typeColors[item.type] || 'bg-gray-100']">
                    {{ typeIcons[item.type] || '📄' }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ item.title }}</p>
                        <div v-if="item.color" class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: item.color }"></div>
                    </div>
                    <p class="text-xs text-gray-500 truncate">{{ item.subtitle }}</p>
                </div>
                <span :class="['text-xs px-2 py-1 rounded-full font-medium capitalize', typeColors[item.type] || 'bg-gray-100 text-gray-600']">
                    {{ item.type }}
                </span>
            </Link>
        </div>

        <div v-else class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <p class="text-gray-500">{{ t('search.start') }}</p>
        </div>
    </div>
</template>
