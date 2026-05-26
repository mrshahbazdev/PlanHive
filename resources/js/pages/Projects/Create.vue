<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const projectColors = ['#14b8a6', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899', '#10b981', '#f97316', '#6366f1'];

const form = useForm({
    name: '',
    description: '',
    color: '#14b8a6',
    status: 'active',
    start_date: '',
    end_date: '',
});

const submit = () => {
    form.post('/projects');
};
</script>

<template>
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <Link href="/projects" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </Link>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('projects.new') }}</h1>
        </div>

        <div class="card p-8">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('projects.name') }} *</label>
                    <input v-model="form.name" type="text" required class="input-field" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('projects.description') }}</label>
                    <textarea v-model="form.description" rows="3" class="input-field"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('projects.color') }}</label>
                    <div class="flex gap-2">
                        <button v-for="color in projectColors" :key="color" type="button"
                                @click="form.color = color"
                                :class="['w-8 h-8 rounded-full border-2 transition-transform', form.color === color ? 'border-gray-900 dark:border-white scale-110' : 'border-transparent']"
                                :style="{ backgroundColor: color }">
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('projects.start_date') }}</label>
                        <input v-model="form.start_date" type="date" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('projects.end_date') }}</label>
                        <input v-model="form.end_date" type="date" class="input-field" />
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <Link href="/projects" class="btn-secondary">{{ t('common.cancel') }}</Link>
                    <button type="submit" :disabled="form.processing" class="btn-primary">{{ t('common.create') }}</button>
                </div>
            </form>
        </div>
    </div>
</template>
