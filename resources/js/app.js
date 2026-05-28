import { createApp, h } from 'vue';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createInertiaApp } from '@inertiajs/vue3';
import { createI18n } from 'vue-i18n';
import { createPinia } from 'pinia';
import en from './locales/en.json';
import de from './locales/de.json';
import AppLayout from './layouts/AppLayout.vue';
import { useThemeStore } from './stores/theme.js';
import { useSidebarStore } from './stores/sidebar.js';

const i18n = createI18n({
    legacy: false,
    locale: document.documentElement.lang || 'en',
    fallbackLocale: 'en',
    messages: { en, de },
});

const pinia = createPinia();

createInertiaApp({
    title: (title) => title ? `${title} - PlanHive` : 'PlanHive',
    resolve: async (name) => {
        const page = await resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue'));
        const authPages = ['Auth/Login', 'Auth/Register', 'Auth/ForgotPassword', 'Auth/ResetPassword', 'Welcome'];
        if (page.default.layout === undefined && !authPages.includes(name)) {
            page.default.layout = AppLayout;
        }
        return page;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .use(pinia);
        app.mount(el);

        // Initialize stores after mount
        useThemeStore().init();
        useSidebarStore().init();
    },
});
