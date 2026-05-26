import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useSidebarStore = defineStore('sidebar', () => {
    const collapsed = ref(false);

    const init = () => {
        const saved = localStorage.getItem('planhive_sidebar_collapsed');
        if (saved !== null) {
            collapsed.value = saved === 'true';
        }
    };

    const toggle = () => {
        collapsed.value = !collapsed.value;
        localStorage.setItem('planhive_sidebar_collapsed', collapsed.value);
    };

    return { collapsed, toggle, init };
});
