<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useThemeStore } from '../stores/theme.js';
import { useSidebarStore } from '../stores/sidebar.js';

const { t, locale } = useI18n();
const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);

const themeStore = useThemeStore();
const sidebarStore = useSidebarStore();
const userMenuOpen = ref(false);

const navigation = [
    { name: 'nav.dashboard', href: '/dashboard', icon: 'calendar' },
    { name: 'nav.projects', href: '/projects', icon: 'folder' },
    { name: 'nav.tasks', href: '/tasks', icon: 'check-square' },
    { name: 'nav.goals', href: '/goals', icon: 'target' },
    { name: 'nav.notes', href: '/notes', icon: 'file-text' },
    { name: 'nav.documents', href: '/documents', icon: 'paperclip' },
    { name: 'nav.contacts', href: '/contacts', icon: 'users' },
    { name: 'Reminders', href: '/reminders', icon: 'bell' },
];

const isActive = (href) => {
    return page.url.startsWith(href);
};

const toggleLocale = () => {
    locale.value = locale.value === 'en' ? 'de' : 'en';
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen flex bg-gray-50 dark:bg-gray-900">
        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-30 flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300',
                sidebarStore.collapsed ? 'w-16' : 'w-64'
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center h-16 px-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-amber-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2L3 7v6l7 5 7-5V7l-7-5zM10 4.236L14.764 7.5 10 10.764 5.236 7.5 10 4.236z"/>
                        </svg>
                    </div>
                    <span v-if="!sidebarStore.collapsed" class="text-lg font-bold text-gray-900 dark:text-white">PlanHive</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                <Link
                    v-for="item in navigation"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                        isActive(item.href)
                            ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400'
                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-white'
                    ]"
                >
                    <svg v-if="item.icon === 'calendar'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <svg v-if="item.icon === 'folder'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <svg v-if="item.icon === 'check-square'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <svg v-if="item.icon === 'target'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><circle cx="12" cy="12" r="6" stroke-width="2"/><circle cx="12" cy="12" r="2" stroke-width="2"/></svg>
                    <svg v-if="item.icon === 'file-text'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <svg v-if="item.icon === 'paperclip'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <svg v-if="item.icon === 'users'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <svg v-if="item.icon === 'bell'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span v-if="!sidebarStore.collapsed">{{ item.name.startsWith('nav.') ? t(item.name) : item.name }}</span>
                </Link>
            </nav>

            <!-- Sidebar Toggle -->
            <div class="p-2 border-t border-gray-200 dark:border-gray-700">
                <button
                    @click="sidebarStore.toggle()"
                    class="w-full flex items-center justify-center p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                    <svg v-if="!sidebarStore.collapsed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div :class="['flex-1 transition-all duration-300', sidebarStore.collapsed ? 'ml-16' : 'ml-64']">
            <!-- Top Header -->
            <header class="sticky top-0 z-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 h-16">
                <div class="flex items-center justify-between h-full px-6">
                    <!-- Search -->
                    <div class="flex-1 max-w-lg">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input
                                type="text"
                                :placeholder="t('common.search')"
                                class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            />
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center gap-3">
                        <!-- Dark Mode Toggle -->
                        <button
                            @click="themeStore.toggle()"
                            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            :title="themeStore.darkMode ? 'Light Mode' : 'Dark Mode'"
                        >
                            <svg v-if="themeStore.darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </button>

                        <!-- Language Toggle -->
                        <button
                            @click="toggleLocale"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            {{ locale === 'en' ? 'DE' : 'EN' }}
                        </button>

                        <!-- Notifications -->
                        <Link href="/notifications" class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </Link>

                        <!-- User Menu -->
                        <div class="relative">
                            <button
                                @click="userMenuOpen = !userMenuOpen"
                                class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                    {{ user?.name?.charAt(0)?.toUpperCase() }}
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 hidden sm:block">{{ user?.name }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div v-if="userMenuOpen" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                                <Link href="/profile" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" @click="userMenuOpen = false">
                                    {{ t('nav.settings') }}
                                </Link>
                                <button @click="logout" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    {{ t('auth.logout') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <div v-if="flash?.success" class="mx-6 mt-4">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg text-sm">
                    {{ flash.success }}
                </div>
            </div>
            <div v-if="flash?.error" class="mx-6 mt-4">
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm">
                    {{ flash.error }}
                </div>
            </div>

            <!-- Page Content -->
            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
