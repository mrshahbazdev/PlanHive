<script setup>
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({ token: String, email: String });

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4">
        <div class="w-full max-w-md">
            <div class="flex items-center justify-center gap-3 mb-8">
                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-amber-500 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2L3 7v6l7 5 7-5V7l-7-5zM10 4.236L14.764 7.5 10 10.764 5.236 7.5 10 4.236z"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-900 dark:text-white">PlanHive</span>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">Reset Password</h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('auth.email') }}</label>
                        <input v-model="form.email" type="email" required class="input-field" />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">New {{ t('auth.password') }}</label>
                        <input v-model="form.password" type="password" required class="input-field" />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('auth.confirm_password') }}</label>
                        <input v-model="form.password_confirmation" type="password" required class="input-field" />
                    </div>
                    <button type="submit" :disabled="form.processing" class="btn-primary w-full py-3">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</template>
