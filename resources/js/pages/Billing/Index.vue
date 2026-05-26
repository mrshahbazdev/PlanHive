<script setup>
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({
    plans: Array,
    currentPlan: String,
    subscription: Object,
    stripeConfigured: Boolean,
});

const subscribe = (planId) => {
    router.post('/billing/subscribe', { plan: planId });
};

const openPortal = () => {
    router.get('/billing/portal');
};

const cancelSubscription = () => {
    if (confirm('Are you sure you want to cancel your subscription?')) {
        router.post('/billing/cancel');
    }
};

const resumeSubscription = () => {
    router.post('/billing/resume');
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('billing.title') }}</h1>
            <button v-if="subscription && stripeConfigured" @click="openPortal" class="btn-secondary text-sm">
                Manage Billing
            </button>
        </div>

        <!-- Current Subscription Info -->
        <div v-if="subscription" class="card p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Current Plan: <span class="text-primary-600 capitalize">{{ currentPlan }}</span></p>
                    <p class="text-xs text-gray-500">Status: {{ subscription.stripe_status }}</p>
                </div>
                <div class="flex gap-2">
                    <button v-if="subscription.on_grace_period" @click="resumeSubscription" class="btn-primary text-sm">Resume</button>
                    <button v-else-if="currentPlan !== 'free'" @click="cancelSubscription" class="btn-danger text-sm">Cancel</button>
                </div>
            </div>
            <p v-if="subscription.ends_at" class="text-xs text-amber-600 mt-2">
                Access until {{ new Date(subscription.ends_at).toLocaleDateString() }}
            </p>
        </div>

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="plan in plans" :key="plan.id"
                 :class="['card p-6 relative', plan.popular ? 'ring-2 ring-primary-500' : '']">
                <div v-if="plan.popular" class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-primary-500 text-white text-xs font-bold rounded-full">
                    Most Popular
                </div>

                <div class="text-center mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ plan.name }}</h3>
                    <div class="mt-3">
                        <span class="text-4xl font-bold text-gray-900 dark:text-white">€{{ plan.price }}</span>
                        <span class="text-gray-500 text-sm">/{{ plan.interval }}</span>
                    </div>
                </div>

                <ul class="space-y-3 mb-6">
                    <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-2 text-sm">
                        <svg class="w-4 h-4 text-primary-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-600 dark:text-gray-400">{{ feature }}</span>
                    </li>
                </ul>

                <div class="mt-auto">
                    <button v-if="plan.id === currentPlan"
                            disabled
                            class="w-full py-2.5 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 cursor-not-allowed">
                        Current Plan
                    </button>
                    <button v-else-if="plan.id === 'free'"
                            disabled
                            class="w-full py-2.5 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 cursor-not-allowed">
                        Free
                    </button>
                    <button v-else-if="!stripeConfigured"
                            disabled
                            class="w-full py-2.5 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-amber-600 cursor-not-allowed">
                        Stripe not configured
                    </button>
                    <button v-else
                            @click="subscribe(plan.id)"
                            :class="[plan.popular ? 'btn-primary' : 'btn-secondary', 'w-full py-2.5']">
                        Upgrade to {{ plan.name }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
