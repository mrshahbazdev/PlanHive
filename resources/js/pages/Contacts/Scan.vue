<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const imagePreview = ref(null);
const scanning = ref(false);
const scanResult = ref(null);
const error = ref(null);
const fileInput = ref(null);

const form = ref({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    company: '',
    job_title: '',
    address: '',
    website: '',
});

const handleFile = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (ev) => {
        imagePreview.value = ev.target.result;
    };
    reader.readAsDataURL(file);
};

const handleDrop = (e) => {
    const file = e.dataTransfer.files?.[0];
    if (!file || !file.type.startsWith('image/')) return;

    const reader = new FileReader();
    reader.onload = (ev) => {
        imagePreview.value = ev.target.result;
    };
    reader.readAsDataURL(file);
};

const scanCard = async () => {
    if (!imagePreview.value) return;
    scanning.value = true;
    error.value = null;

    try {
        const Tesseract = await import('tesseract.js');
        const { data: { text } } = await Tesseract.recognize(imagePreview.value, 'eng+deu', {
            logger: () => {},
        });

        scanResult.value = text;
        parseBusinessCard(text);
    } catch (err) {
        error.value = 'OCR scan failed. Please try a clearer image.';
        console.error(err);
    } finally {
        scanning.value = false;
    }
};

const parseBusinessCard = (text) => {
    const lines = text.split('\n').map(l => l.trim()).filter(Boolean);

    const emailMatch = text.match(/[\w.+-]+@[\w.-]+\.\w{2,}/);
    if (emailMatch) form.value.email = emailMatch[0];

    const phoneMatch = text.match(/[\+]?[(]?[0-9]{1,4}[)]?[-\s./0-9]{6,}/);
    if (phoneMatch) form.value.phone = phoneMatch[0].trim();

    const urlMatch = text.match(/(?:www\.|https?:\/\/)[\w.-]+\.\w{2,}[^\s]*/i);
    if (urlMatch) {
        let url = urlMatch[0];
        if (!url.startsWith('http')) url = 'https://' + url;
        form.value.website = url;
    }

    const nonDataLines = lines.filter(line => {
        return !line.match(/[\w.+-]+@/) &&
               !line.match(/[\+]?[(]?[0-9]{3}/) &&
               !line.match(/www\./i) &&
               !line.match(/https?:\/\//i);
    });

    if (nonDataLines.length > 0) {
        const nameParts = nonDataLines[0].split(/\s+/);
        if (nameParts.length >= 2) {
            form.value.first_name = nameParts[0];
            form.value.last_name = nameParts.slice(1).join(' ');
        } else {
            form.value.first_name = nonDataLines[0];
        }
    }

    if (nonDataLines.length > 1) {
        form.value.job_title = nonDataLines[1];
    }

    if (nonDataLines.length > 2) {
        form.value.company = nonDataLines[2];
    }

    if (nonDataLines.length > 3) {
        form.value.address = nonDataLines.slice(3).join(', ');
    }
};

const saveContact = () => {
    router.post('/contacts', form.value);
};

const reset = () => {
    imagePreview.value = null;
    scanResult.value = null;
    error.value = null;
    form.value = {
        first_name: '', last_name: '', email: '', phone: '',
        company: '', job_title: '', address: '', website: '',
    };
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('contacts.scan_card') }}</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left: Image Upload & Preview -->
            <div class="space-y-4">
                <div v-if="!imagePreview"
                     @dragover.prevent
                     @drop.prevent="handleDrop"
                     @click="fileInput?.click()"
                     class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-12 text-center cursor-pointer hover:border-primary-400 transition-colors">
                    <input ref="fileInput" type="file" accept="image/*" capture="environment" class="hidden" @change="handleFile" />
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('contacts.scan_card') }}</p>
                    <p class="text-xs text-gray-400 mt-1">Take a photo or upload an image of a business card</p>
                </div>

                <div v-else class="space-y-4">
                    <div class="relative">
                        <img :src="imagePreview" class="w-full rounded-xl border border-gray-200 dark:border-gray-700" />
                        <button @click="reset" class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <button v-if="!scanResult" @click="scanCard" :disabled="scanning" class="btn-primary w-full py-3">
                        <svg v-if="scanning" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                        {{ scanning ? 'Scanning...' : 'Scan Business Card' }}
                    </button>

                    <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg text-sm">
                        {{ error }}
                    </div>

                    <div v-if="scanResult" class="card p-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Raw OCR Text</h3>
                        <pre class="text-xs text-gray-500 whitespace-pre-wrap bg-gray-50 dark:bg-gray-900 rounded-lg p-3">{{ scanResult }}</pre>
                    </div>
                </div>
            </div>

            <!-- Right: Extracted Data Form -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Contact Details</h3>
                <form @submit.prevent="saveContact" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.first_name') }}</label>
                            <input v-model="form.first_name" type="text" required class="input-field" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.last_name') }}</label>
                            <input v-model="form.last_name" type="text" class="input-field" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.email') }}</label>
                        <input v-model="form.email" type="email" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.phone') }}</label>
                        <input v-model="form.phone" type="tel" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.company') }}</label>
                        <input v-model="form.company" type="text" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.job_title') }}</label>
                        <input v-model="form.job_title" type="text" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.website') }}</label>
                        <input v-model="form.website" type="url" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('contacts.address') }}</label>
                        <textarea v-model="form.address" rows="2" class="input-field"></textarea>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="btn-primary flex-1">{{ t('common.save') }}</button>
                        <button type="button" @click="reset" class="btn-secondary">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
