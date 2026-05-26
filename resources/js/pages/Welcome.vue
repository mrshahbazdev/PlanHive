<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
        <!-- Header -->
        <header class="flex items-center justify-between px-8 py-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-amber-500 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2L3 7v6l7 5 7-5V7l-7-5zM10 4.236L14.764 7.5 10 10.764 5.236 7.5 10 4.236z"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-white">PlanHive</span>
            </div>
            <div class="flex items-center gap-4">
                <Link href="/login" class="px-5 py-2.5 text-sm font-medium text-gray-300 hover:text-white transition-colors">
                    {{ t('auth.login') }}
                </Link>
                <Link href="/register" class="px-5 py-2.5 text-sm font-medium bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    {{ t('auth.register') }}
                </Link>
            </div>
        </header>

        <!-- Hero -->
        <main class="flex flex-col items-center justify-center px-8 pt-20 pb-32 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-900/30 border border-primary-700/30 text-primary-400 text-sm font-medium mb-8">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Multi-Project Management SaaS
            </div>

            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold text-white leading-tight max-w-4xl">
                {{ t('app.tagline') }}
            </h1>

            <p class="mt-6 text-lg text-gray-400 max-w-2xl">
                Manage multiple projects, track tasks, set reminders, take notes, upload documents, and sync with Outlook & Teams — all from one beautiful calendar-first dashboard.
            </p>

            <div class="flex items-center gap-4 mt-10">
                <Link href="/register" class="px-8 py-3.5 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors text-lg shadow-lg shadow-primary-500/25">
                    {{ t('auth.sign_up') }} — {{ t('common.free') || 'Free' }}
                </Link>
                <Link href="/login" class="px-8 py-3.5 bg-white/10 text-white font-semibold rounded-xl hover:bg-white/20 transition-colors text-lg border border-white/10">
                    {{ t('auth.sign_in') }}
                </Link>
            </div>

            <!-- Feature Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-24 max-w-5xl w-full">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 text-left">
                    <div class="w-12 h-12 bg-primary-500/20 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">{{ t('nav.calendar') }}</h3>
                    <p class="text-gray-400 text-sm">Color-coded calendar as your homepage. See all projects, tasks, and meetings at a glance.</p>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 text-left">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">{{ t('nav.tasks') }} & {{ t('nav.goals') }}</h3>
                    <p class="text-gray-400 text-sm">Kanban boards, priorities, subtasks, and goal tracking across all your projects.</p>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 text-left">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">{{ t('nav.contacts') }}</h3>
                    <p class="text-gray-400 text-sm">Manage contacts, scan business cards with OCR, and integrate with Outlook & Teams.</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="text-center py-8 text-gray-500 text-sm border-t border-white/5">
            &copy; {{ new Date().getFullYear() }} PlanHive. Built with Laravel & Vue.
        </footer>
    </div>
</template>
