<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $plans = [
            [
                'id' => 'free',
                'name' => 'Free',
                'price' => 0,
                'interval' => 'month',
                'features' => [
                    '3 Projects',
                    '10 Tasks per project',
                    'Calendar view',
                    'Basic notes',
                    'Community support',
                ],
            ],
            [
                'id' => 'pro',
                'name' => 'Pro',
                'price' => 9,
                'interval' => 'month',
                'stripe_price_id' => config('billing.pro_monthly_price_id', ''),
                'features' => [
                    'Unlimited projects',
                    'Unlimited tasks',
                    'Rich text notes (Tiptap)',
                    'Document upload (5GB)',
                    'Outlook calendar sync',
                    'Business card OCR',
                    'Email reminders',
                    'Priority support',
                ],
                'popular' => true,
            ],
            [
                'id' => 'business',
                'name' => 'Business',
                'price' => 24,
                'interval' => 'month',
                'stripe_price_id' => config('billing.business_monthly_price_id', ''),
                'features' => [
                    'Everything in Pro',
                    'Unlimited document storage',
                    'Teams integration',
                    'Push notifications',
                    'Advanced analytics',
                    'Team management (50 members)',
                    'API access',
                    'Dedicated support',
                ],
            ],
        ];

        $subscription = null;
        $currentPlan = 'free';

        if (method_exists($user, 'subscribed') && $user->subscribed('default')) {
            $sub = $user->subscription('default');
            $subscription = [
                'name' => $sub->name,
                'stripe_status' => $sub->stripe_status,
                'ends_at' => $sub->ends_at,
                'on_grace_period' => $sub->onGracePeriod(),
            ];

            foreach ($plans as $plan) {
                if (isset($plan['stripe_price_id']) && $sub->hasPrice($plan['stripe_price_id'])) {
                    $currentPlan = $plan['id'];
                    break;
                }
            }
        }

        return Inertia::render('Billing/Index', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
            'subscription' => $subscription,
            'stripeConfigured' => !empty(config('services.stripe.key')),
        ]);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan' => ['required', 'in:pro,business'],
        ]);

        $priceId = $request->plan === 'pro'
            ? config('billing.pro_monthly_price_id')
            : config('billing.business_monthly_price_id');

        if (!$priceId) {
            return back()->with('error', 'Stripe not configured. Please add billing price IDs.');
        }

        return $request->user()
            ->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('billing.index') . '?success=1',
                'cancel_url' => route('billing.index') . '?cancelled=1',
            ]);
    }

    public function portal(Request $request)
    {
        return $request->user()->redirectToBillingPortal(route('billing.index'));
    }

    public function cancel(Request $request)
    {
        if ($request->user()->subscribed('default')) {
            $request->user()->subscription('default')->cancel();
        }

        return back()->with('success', 'Subscription cancelled. You will retain access until the end of your billing period.');
    }

    public function resume(Request $request)
    {
        $subscription = $request->user()->subscription('default');

        if ($subscription && $subscription->onGracePeriod()) {
            $subscription->resume();
        }

        return back()->with('success', 'Subscription resumed!');
    }
}
