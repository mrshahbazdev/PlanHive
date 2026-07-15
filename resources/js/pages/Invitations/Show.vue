<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({
    invitation: { type: Object, required: true },
    token: { type: String, required: true },
});

const user = computed(() => usePage().props.auth?.user);
const acceptForm = useForm({});

const submitAccept = () => {
    acceptForm.post(`/invitations/${props.token}/accept`);
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4">
        <div class="w-full max-w-lg">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
                <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ t('invitations.title', { project: invitation.project.name }) }}
                </h2>

                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    {{ t('invitations.description', { name: invitation.inviter.name, role: invitation.role, project: invitation.project.name }) }}
                </p>

                <div v-if="user" class="space-y-4">
                    <template v-if="user.email === invitation.email">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ t('invitations.signed_in_as', { email: user.email }) }}
                        </p>
                        <form @submit.prevent="submitAccept">
                            <button type="submit" :disabled="acceptForm.processing" class="btn-primary w-full py-3">
                                {{ t('invitations.accept') }}
                            </button>
                        </form>
                    </template>
                    <template v-else>
                        <p class="text-sm text-red-500">
                            {{ t('invitations.wrong_account', { invitation_email: invitation.email, user_email: user.email }) }}
                        </p>
                        <form method="POST" action="/logout">
                            <button type="submit" class="text-primary-600 hover:text-primary-700 font-medium">
                                {{ t('invitations.sign_out') }}
                            </button>
                        </form>
                    </template>
                </div>

                <div v-else class="space-y-3">
                    <Link :href="`/register?invitation_token=${token}`" class="btn-primary w-full py-3 block">
                        {{ t('invitations.create_account') }}
                    </Link>
                    <Link :href="`/login?redirect=/invitations/${token}`" class="btn-secondary w-full py-3 block">
                        {{ t('invitations.sign_in') }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
