<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';

const { t, locale } = useI18n();
const page = usePage();

const profileForm = useForm({
    name: page.props.auth.user.name,
    email: page.props.auth.user.email,
    locale: page.props.auth.user.locale || 'en',
    timezone: page.props.auth.user.timezone || 'Europe/Berlin',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const avatarInput = ref(null);

const updateProfile = () => {
    profileForm.put('/profile', {
        onSuccess: () => {
            locale.value = profileForm.locale;
        },
    });
};

const updatePassword = () => {
    passwordForm.put('/profile/password', {
        onSuccess: () => passwordForm.reset(),
    });
};

const uploadAvatar = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('avatar', file);
    const form = useForm(formData);
    form.post('/profile/avatar', { forceFormData: true });
};

const timezones = [
    'Europe/Berlin', 'Europe/London', 'Europe/Paris', 'Europe/Rome', 'Europe/Madrid',
    'Europe/Amsterdam', 'Europe/Vienna', 'Europe/Zurich', 'Europe/Warsaw', 'Europe/Prague',
    'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles',
    'Asia/Tokyo', 'Asia/Shanghai', 'Asia/Dubai', 'Asia/Kolkata',
    'Australia/Sydney', 'Pacific/Auckland', 'UTC',
];
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('nav.settings') }}</h1>

        <!-- Profile Information -->
        <div class="card p-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Profile Information</h2>
            <form @submit.prevent="updateProfile" class="space-y-5">
                <!-- Avatar -->
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-primary-500 rounded-full flex items-center justify-center text-white text-xl font-semibold">
                        {{ profileForm.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div>
                        <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="uploadAvatar" />
                        <button type="button" @click="avatarInput?.click()" class="btn-secondary text-sm">Change Avatar</button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('auth.name') }}</label>
                    <input v-model="profileForm.name" type="text" required class="input-field" />
                    <p v-if="profileForm.errors.name" class="mt-1 text-sm text-red-500">{{ profileForm.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('auth.email') }}</label>
                    <input v-model="profileForm.email" type="email" required class="input-field" />
                    <p v-if="profileForm.errors.email" class="mt-1 text-sm text-red-500">{{ profileForm.errors.email }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('auth.language') }}</label>
                        <select v-model="profileForm.locale" class="input-field">
                            <option value="en">English</option>
                            <option value="de">Deutsch</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Timezone</label>
                        <select v-model="profileForm.timezone" class="input-field">
                            <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" :disabled="profileForm.processing" class="btn-primary">{{ t('common.save') }}</button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="card p-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Change Password</h2>
            <form @submit.prevent="updatePassword" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Current Password</label>
                    <input v-model="passwordForm.current_password" type="password" required class="input-field" />
                    <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-500">{{ passwordForm.errors.current_password }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">New Password</label>
                    <input v-model="passwordForm.password" type="password" required class="input-field" />
                    <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-500">{{ passwordForm.errors.password }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('auth.confirm_password') }}</label>
                    <input v-model="passwordForm.password_confirmation" type="password" required class="input-field" />
                </div>
                <div class="flex justify-end pt-4">
                    <button type="submit" :disabled="passwordForm.processing" class="btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</template>
