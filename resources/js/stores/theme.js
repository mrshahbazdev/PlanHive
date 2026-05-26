import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useThemeStore = defineStore('theme', () => {
    const darkMode = ref(false);

    const init = () => {
        const saved = localStorage.getItem('planhive_dark_mode');
        if (saved !== null) {
            darkMode.value = saved === 'true';
        } else {
            darkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        applyTheme();
    };

    const toggle = () => {
        darkMode.value = !darkMode.value;
        localStorage.setItem('planhive_dark_mode', darkMode.value);
        applyTheme();
    };

    const applyTheme = () => {
        if (darkMode.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    return { darkMode, toggle, init };
});
