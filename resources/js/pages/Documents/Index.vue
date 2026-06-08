<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({ documents: Object, folders: { type: Array, default: () => [] }, filters: Object });

const dragOver = ref(false);
const previewDoc = ref(null);
const activeFolder = ref(props.filters?.folder || null);
const search = ref(props.filters?.search || '');
const showFolderInput = ref(false);
const newFolderName = ref('');
const uploadFolder = ref(null);

const handleDrop = (e) => {
    dragOver.value = false;
    const file = e.dataTransfer.files[0];
    if (file) uploadFile(file);
};

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (file) uploadFile(file);
};

const uploadFile = (file) => {
    const formData = new FormData();
    formData.append('file', file);
    if (uploadFolder.value || activeFolder.value) {
        formData.append('folder', uploadFolder.value || activeFolder.value);
    }
    router.post('/documents', formData, { forceFormData: true });
};

const deleteDoc = (id) => {
    if (confirm(t('common.confirm_delete'))) {
        router.delete(`/documents/${id}`);
    }
};

const filterByFolder = (folder) => {
    activeFolder.value = folder;
    const params = {};
    if (folder) params.folder = folder;
    if (search.value) params.search = search.value;
    router.get('/documents', params, { preserveState: true });
};

const doSearch = () => {
    const params = {};
    if (activeFolder.value) params.folder = activeFolder.value;
    if (search.value) params.search = search.value;
    router.get('/documents', params, { preserveState: true });
};

const createFolder = () => {
    if (newFolderName.value.trim()) {
        uploadFolder.value = newFolderName.value.trim();
        newFolderName.value = '';
        showFolderInput.value = false;
    }
};

const isPreviewable = (mime) => {
    return ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'application/pdf'].includes(mime);
};

const isImage = (mime) => mime?.startsWith('image/');

const formatSize = (bytes) => {
    if (!bytes) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    let i = 0;
    let size = bytes;
    while (size >= 1024 && i < units.length - 1) { size /= 1024; i++; }
    return `${size.toFixed(1)} ${units[i]}`;
};

const fileIcon = (mime) => {
    if (mime?.startsWith('image/')) return 'image';
    if (mime === 'application/pdf') return 'pdf';
    if (mime?.includes('word') || mime?.includes('document')) return 'doc';
    if (mime?.includes('sheet') || mime?.includes('excel')) return 'sheet';
    return 'file';
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('nav.documents') }}</h1>
        </div>

        <!-- Search & Folder Filter -->
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="relative flex-1 max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input v-model="search" type="text" :placeholder="t('common.search')" class="input-field pl-10" @keyup.enter="doSearch" />
            </div>
            <div class="flex gap-2 flex-wrap items-center">
                <button @click="filterByFolder(null)"
                    :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-colors',
                        !activeFolder ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200']">
                    {{ t('common.all') || 'All' }}
                </button>
                <button v-for="folder in folders" :key="folder" @click="filterByFolder(folder)"
                    :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-colors',
                        activeFolder === folder ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200']">
                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    {{ folder }}
                </button>
                <button v-if="!showFolderInput" @click="showFolderInput = true" class="px-2 py-1.5 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600">
                    + Folder
                </button>
                <div v-else class="flex items-center gap-1">
                    <input v-model="newFolderName" type="text" class="input-field text-xs py-1 px-2 w-32" placeholder="Folder name" @keyup.enter="createFolder" />
                    <button @click="createFolder" class="btn-primary text-xs px-2 py-1">OK</button>
                    <button @click="showFolderInput = false" class="text-gray-400 hover:text-gray-600 text-xs px-1">X</button>
                </div>
            </div>
        </div>

        <!-- Upload Zone -->
        <div
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="handleDrop"
            :class="['border-2 border-dashed rounded-xl p-8 text-center mb-6 transition-colors cursor-pointer',
                      dragOver ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/10' : 'border-gray-300 dark:border-gray-600 hover:border-primary-400']"
            @click="$refs.fileInput.click()"
        >
            <input ref="fileInput" type="file" class="hidden" @change="handleFileSelect" />
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('documents.drag_drop') }}</p>
            <p v-if="activeFolder || uploadFolder" class="text-xs text-primary-500 mt-1">Uploading to: {{ uploadFolder || activeFolder }}</p>
            <p class="text-xs text-gray-400 mt-1">Max 50MB</p>
        </div>

        <!-- Document List -->
        <div v-if="!documents?.data?.length" class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-gray-500">{{ t('documents.no_documents') }}</p>
        </div>

        <div v-else class="space-y-2">
            <div v-for="doc in documents.data" :key="doc.id" class="card p-4 flex items-center gap-4">
                <!-- Icon / Thumbnail -->
                <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0 overflow-hidden">
                    <img v-if="isImage(doc.mime_type)" :src="`/documents/${doc.id}/preview`" class="w-full h-full object-cover rounded-lg" />
                    <svg v-else-if="fileIcon(doc.mime_type) === 'pdf'" class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zm-2.5 9.5a1 1 0 011-1h1a1 1 0 010 2h-1v1h1a1 1 0 010 2h-1a1 1 0 01-1-1v-3z"/></svg>
                    <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ doc.original_name }}</p>
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <span>{{ formatSize(doc.size_bytes) }}</span>
                        <span v-if="doc.folder" class="flex items-center gap-1 text-primary-500">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            {{ doc.folder }}
                        </span>
                        <span>{{ doc.mime_type }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-1">
                    <button v-if="isPreviewable(doc.mime_type)" @click="previewDoc = doc"
                            class="p-2 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                            title="Preview">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                    <a :href="`/documents/${doc.id}/download`"
                       class="p-2 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                       title="Download">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    <button @click="deleteDoc(doc.id)"
                            class="p-2 rounded-lg text-gray-400 hover:text-red-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div v-if="previewDoc" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click.self="previewDoc = null">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ previewDoc.original_name }}</h3>
                    <button @click="previewDoc = null" class="p-1 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 overflow-auto max-h-[calc(90vh-80px)]">
                    <img v-if="isImage(previewDoc.mime_type)" :src="`/documents/${previewDoc.id}/preview`" class="max-w-full mx-auto rounded-lg" />
                    <iframe v-else-if="previewDoc.mime_type === 'application/pdf'" :src="`/documents/${previewDoc.id}/preview`" class="w-full h-[70vh] border-0 rounded-lg"></iframe>
                </div>
            </div>
        </div>
    </div>
</template>
