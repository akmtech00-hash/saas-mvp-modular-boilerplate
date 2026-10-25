<?php

declare(strict_types=1);

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Models\Subscription;
use App\Modules\Metering\Models\UsageMeter;
use App\Modules\Workspace\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeBillingService
{
    /**
     * Verify Stripe webhook signature and process event payload.
     *
     * @throws SignatureVerificationException|\UnexpectedValueException
     */
    public function verifyAndHandleWebhook(string $payload, ?string $signatureHeader): array
    {
        $secret = config('billing.stripe.webhook_secret');

        if (!empty($secret) && $secret !== 'whsec_placeholder') {
            if (!$signatureHeader) {
                throw new \InvalidArgumentException('Missing Stripe-Signature header.');
            }
            $event = Webhook::constructEvent($payload, $signatureHeader, $secret);
            $eventData = json_decode($payload, true);
        } else {
            // Local fallback / development testing without active Stripe CLI secret
            $eventData = json_decode($payload, true);
            if (!isset($eventData['type'])) {
                throw new \InvalidArgumentException('Invalid webhook event payload.');
            }
        }

        $eventType = $eventData['type'];
        Log::info("Stripe webhook received: {$eventType}", ['event_id' => $eventData['id'] ?? null]);

        $result = match ($eventType) {
            'customer.subscription.created',
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($eventData),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($eventData),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($eventData),
            default => ['status' => 'ignored', 'event' => $eventType],
        };

        return [
            'status' => 'success',
            'event' => $eventType,
            'result' => $result,
        ];
    }

    /**
     * Create or update subscription record from incoming Stripe webhook payload.
     */
    public function handleSubscriptionUpdated(array $payload): Subscription
    {
        $stripeSub = $payload['data']['object'];
        $workspaceId = $stripeSub['metadata']['workspace_id'] ?? null;

        if (!$workspaceId) {
            throw new \InvalidArgumentException("Stripe subscription missing 'workspace_id' metadata.");
        }

        $priceId = $stripeSub['items']['data'][0]['price']['id'] ?? null;
        $tier = $this->determineTierFromPrice($priceId);

        $subscription = Subscription::updateOrCreate(
            ['stripe_id' => $stripeSub['id']],
            [
                'workspace_id' => $workspaceId,
                'type' => $tier,
                'stripe_status' => $stripeSub['status'],
                'stripe_price' => $priceId,
                'quantity' => $stripeSub['items']['data'][0]['quantity'] ?? 1,
                'trial_ends_at' => isset($stripeSub['trial_end']) ? Carbon::createFromTimestamp($stripeSub['trial_end']) : null,
                'ends_at' => isset($stripeSub['cancel_at']) ? Carbon::createFromTimestamp($stripeSub['cancel_at']) : null,
            ]
        );

        // Update allocated AI credit quota for the new tier
        $this->syncWorkspaceQuota($workspaceId, $tier);

        return $subscription;
    }

    /**
     * Handle subscription cancellation or deletion.
     */
    public function handleSubscriptionDeleted(array $payload): Subscription
    {
        $stripeSub = $payload['data']['object'];

        /** @var Subscription $subscription */
        $subscription = Subscription::where('stripe_id', $stripeSub['id'])->firstOrFail();
        $subscription->update([
            'stripe_status' => 'canceled',
            'ends_at' => now(),
        ]);

        return $subscription;
    }

    /**
     * Handle failed payment invoices.
     */
    public function handleInvoicePaymentFailed(array $payload): array
    {
        $invoice = $payload['data']['object'];
        Log::warning('Stripe invoice payment failed', [
            'invoice_id' => $invoice['id'] ?? null,
            'customer' => $invoice['customer'] ?? null,
            'amount_due' => $invoice['amount_due'] ?? null,
        ]);

        return ['status' => 'payment_failed_logged'];
    }

    /**
     * Determine subscription tier identifier from Stripe price ID.
     */
    private function determineTierFromPrice(?string $priceId): string
    {
        if (!$priceId) {
            return 'starter';
        }

        $plans = config('billing.plans', []);
        foreach ($plans as $tierKey => $plan) {
            if (($plan['stripe_price_id'] ?? null) === $priceId) {
                return $tierKey;
            }
        }

        return 'pro';
    }

    /**
     * Adjust workspace usage meters based on plan tier allowance.
     */
    private function syncWorkspaceQuota(string $workspaceId, string $tier): void
    {
        $plans = config('billing.plans', []);
        $allocatedLimit = $plans[$tier]['features']['ai_credits_monthly'] ?? 100;

        UsageMeter::updateOrCreate(
            ['workspace_id' => $workspaceId, 'metric_name' => 'ai_credits'],
            [
                'allocated_limit' => $allocatedLimit,
                'cycle_resets_at' => now()->addMonth(),
            ]
        );
    }
}
