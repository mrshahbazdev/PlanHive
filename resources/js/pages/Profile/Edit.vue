<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';

const { t, locale } = useI18n();
const page = usePage();

const user = page.props.auth.user;

const profileForm = useForm({
    name: user.name,
    email: user.email,
    locale: user.locale || 'en',
    timezone: user.timezone || 'Europe/Berlin',
});

const smtpForm = useForm({
    smtp_host: user.smtp_host || '',
    smtp_port: user.smtp_port || '',
    smtp_username: user.smtp_username || '',
    smtp_password: '',
    smtp_encryption: user.smtp_encryption || 'tls',
    smtp_from_address: user.smtp_from_address || '',
    smtp_from_name: user.smtp_from_name || '',
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

const updateSmtp = () => {
    smtpForm.put('/profile/smtp');
};

const sendTestEmail = () => {
    smtpForm.post('/profile/smtp/test', { preserveScroll: true });
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
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ t('profile.profile_info') }}</h2>
            <form @submit.prevent="updateProfile" class="space-y-5">
                <!-- Avatar -->
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-primary-500 rounded-full flex items-center justify-center text-white text-xl font-semibold">
                        {{ profileForm.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div>
                        <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="uploadAvatar" />
                        <button type="button" @click="avatarInput?.click()" class="btn-secondary text-sm">{{ t('profile.change_avatar') }}</button>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('profile.timezone') }}</label>
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

        <!-- SMTP Settings -->
        <div class="card p-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ t('profile.smtp_settings') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ t('profile.smtp_help') }}</p>

            <form @submit.prevent="updateSmtp" class="space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('profile.smtp_host') }}</label>
                        <input v-model="smtpForm.smtp_host" type="text" class="input-field" placeholder="smtp.example.com" />
                        <p v-if="smtpForm.errors.smtp_host" class="mt-1 text-sm text-red-500">{{ smtpForm.errors.smtp_host }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('profile.smtp_port') }}</label>
                        <input v-model="smtpForm.smtp_port" type="text" class="input-field" placeholder="587" />
                        <p v-if="smtpForm.errors.smtp_port" class="mt-1 text-sm text-red-500">{{ smtpForm.errors.smtp_port }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('profile.smtp_username') }}</label>
                        <input v-model="smtpForm.smtp_username" type="text" class="input-field" />
                        <p v-if="smtpForm.errors.smtp_username" class="mt-1 text-sm text-red-500">{{ smtpForm.errors.smtp_username }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('profile.smtp_password') }}</label>
                        <input v-model="smtpForm.smtp_password" type="password" class="input-field" />
                        <p v-if="smtpForm.errors.smtp_password" class="mt-1 text-sm text-red-500">{{ smtpForm.errors.smtp_password }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('profile.smtp_encryption') }}</label>
                    <select v-model="smtpForm.smtp_encryption" class="input-field">
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="null">None</option>
                    </select>
                    <p v-if="smtpForm.errors.smtp_encryption" class="mt-1 text-sm text-red-500">{{ smtpForm.errors.smtp_encryption }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('profile.smtp_from_address') }}</label>
                        <input v-model="smtpForm.smtp_from_address" type="email" class="input-field" />
                        <p v-if="smtpForm.errors.smtp_from_address" class="mt-1 text-sm text-red-500">{{ smtpForm.errors.smtp_from_address }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('profile.smtp_from_name') }}</label>
                        <input v-model="smtpForm.smtp_from_name" type="text" class="input-field" />
                        <p v-if="smtpForm.errors.smtp_from_name" class="mt-1 text-sm text-red-500">{{ smtpForm.errors.smtp_from_name }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="sendTestEmail" :disabled="smtpForm.processing" class="btn-secondary">{{ t('profile.send_test_email') }}</button>
                    <button type="submit" :disabled="smtpForm.processing" class="btn-primary">{{ t('common.save') }}</button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="card p-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ t('profile.change_password') }}</h2>
            <form @submit.prevent="updatePassword" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('profile.current_password') }}</label>
                    <input v-model="passwordForm.current_password" type="password" required class="input-field" />
                    <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-500">{{ passwordForm.errors.current_password }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('profile.new_password') }}</label>
                    <input v-model="passwordForm.password" type="password" required class="input-field" />
                    <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-500">{{ passwordForm.errors.password }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ t('auth.confirm_password') }}</label>
                    <input v-model="passwordForm.password_confirmation" type="password" required class="input-field" />
                </div>
                <div class="flex justify-end pt-4">
                    <button type="submit" :disabled="passwordForm.processing" class="btn-primary">{{ t('profile.update_password') }}</button>
                </div>
            </form>
        </div>
    </div>
</template>
