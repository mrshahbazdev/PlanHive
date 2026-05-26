<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
defineProps({ documents: Object });

const uploading = ref(false);
const fileInput = ref(null);

const uploadFile = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    uploading.value = true;
    const formData = new FormData();
    formData.append('file', file);

    router.post('/documents', formData, {
        forceFormData: true,
        onFinish: () => { uploading.value = false; },
    });
};

const deleteDocument = (id) => {
    if (confirm(t('common.confirm_delete'))) {
        router.delete(`/documents/${id}`);
    }
};

const formatSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const getFileIcon = (mime) => {
    if (mime?.startsWith('image/')) return 'image';
    if (mime?.includes('pdf')) return 'pdf';
    return 'file';
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('documents.title') }}</h1>
            <div>
                <input ref="fileInput" type="file" class="hidden" @change="uploadFile" />
                <button @click="fileInput?.click()" :disabled="uploading" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    {{ uploading ? t('common.loading') : t('documents.upload') }}
                </button>
            </div>
        </div>

        <!-- Drop Zone -->
        <div class="card p-8 mb-6 border-2 border-dashed border-gray-300 dark:border-gray-600 text-center cursor-pointer hover:border-primary-400 transition-colors"
             @click="fileInput?.click()">
            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            <p class="text-gray-500 dark:text-gray-400">{{ t('documents.drag_drop') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ t('documents.max_size') }}</p>
        </div>

        <div v-if="!documents?.data?.length" class="card p-12 text-center">
            <p class="text-gray-500">{{ t('documents.no_documents') }}</p>
        </div>

        <div v-else class="space-y-2">
            <div v-for="doc in documents.data" :key="doc.id" class="card p-4 flex items-center gap-4">
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg v-if="getFileIcon(doc.mime_type) === 'pdf'" class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                    <svg v-else-if="getFileIcon(doc.mime_type) === 'image'" class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                    <svg v-else class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ doc.original_name }}</p>
                    <p class="text-xs text-gray-400">{{ formatSize(doc.size_bytes) }} &middot; {{ new Date(doc.created_at).toLocaleDateString() }}</p>
                </div>
                <div class="flex gap-2">
                    <a :href="`/documents/${doc.id}/download`" class="btn-secondary text-xs py-1 px-3">{{ t('documents.download') }}</a>
                    <button @click="deleteDocument(doc.id)" class="text-red-500 hover:text-red-700 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
