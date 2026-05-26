<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import { watch } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Start writing...' },
});

const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
        Placeholder.configure({ placeholder: props.placeholder }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm dark:prose-invert max-w-none focus:outline-none min-h-[200px] p-4',
        },
    },
    onUpdate: () => {
        emit('update:modelValue', editor.value.getHTML());
    },
});

watch(() => props.modelValue, (val) => {
    if (editor.value && editor.value.getHTML() !== val) {
        editor.value.commands.setContent(val, false);
    }
});
</script>

<template>
    <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden bg-white dark:bg-gray-800">
        <!-- Toolbar -->
        <div v-if="editor" class="flex items-center gap-1 px-3 py-2 border-b border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 flex-wrap">
            <button type="button" @click="editor.chain().focus().toggleBold().run()"
                    :class="['p-1.5 rounded', editor.isActive('bold') ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"/><path d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"/></svg>
            </button>
            <button type="button" @click="editor.chain().focus().toggleItalic().run()"
                    :class="['p-1.5 rounded', editor.isActive('italic') ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg>
            </button>
            <button type="button" @click="editor.chain().focus().toggleStrike().run()"
                    :class="['p-1.5 rounded', editor.isActive('strike') ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 4H9a3 3 0 100 6h6a3 3 0 110 6H8"/><line x1="4" y1="12" x2="20" y2="12"/></svg>
            </button>
            <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>
            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                    :class="['p-1.5 rounded text-xs font-bold', editor.isActive('heading', { level: 2 }) ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600']">
                H2
            </button>
            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                    :class="['p-1.5 rounded text-xs font-bold', editor.isActive('heading', { level: 3 }) ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600']">
                H3
            </button>
            <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>
            <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                    :class="['p-1.5 rounded', editor.isActive('bulletList') ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="4" cy="6" r="1" fill="currentColor"/><circle cx="4" cy="12" r="1" fill="currentColor"/><circle cx="4" cy="18" r="1" fill="currentColor"/></svg>
            </button>
            <button type="button" @click="editor.chain().focus().toggleOrderedList().run()"
                    :class="['p-1.5 rounded', editor.isActive('orderedList') ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><text x="2" y="8" font-size="8" fill="currentColor">1</text><text x="2" y="14" font-size="8" fill="currentColor">2</text><text x="2" y="20" font-size="8" fill="currentColor">3</text></svg>
            </button>
            <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>
            <button type="button" @click="editor.chain().focus().toggleBlockquote().run()"
                    :class="['p-1.5 rounded', editor.isActive('blockquote') ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 17h3l2-4V7H5v6h3M14 17h3l2-4V7h-6v6h3"/></svg>
            </button>
            <button type="button" @click="editor.chain().focus().toggleCodeBlock().run()"
                    :class="['p-1.5 rounded', editor.isActive('codeBlock') ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="16,18 22,12 16,6"/><polyline points="8,6 2,12 8,18"/></svg>
            </button>
            <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>
            <button type="button" @click="editor.chain().focus().undo().run()" class="p-1.5 rounded text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h10a5 5 0 015 5v0a5 5 0 01-5 5H3"/><polyline points="7,6 3,10 7,14"/></svg>
            </button>
            <button type="button" @click="editor.chain().focus().redo().run()" class="p-1.5 rounded text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10H11a5 5 0 00-5 5v0a5 5 0 005 5h10"/><polyline points="17,6 21,10 17,14"/></svg>
            </button>
        </div>
        <!-- Editor Content -->
        <EditorContent :editor="editor" />
    </div>
</template>
