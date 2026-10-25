<?php

declare(strict_types=1);

namespace App\Modules\Billing\Middleware;

use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    private const TIER_LEVELS = [
        'starter' => 1,
        'pro' => 2,
        'enterprise' => 3,
    ];

    /**
     * Ensure current workspace has an active subscription plan matching the required tier.
     */
    public function handle(Request $request, Closure $next, ?string $planTier = null): Response
    {
        /** @var Workspace|null $workspace */
        $workspace = app(TenantContext::class)->getWorkspace() 
            ?? $request->attributes->get('current_workspace');

        if (!$workspace) {
            return response()->json([
                'error' => 'workspace_required',
                'message' => 'Workspace context required. Provide X-Workspace-Id header.',
            ], 400);
        }

        $subscription = $workspace->activeSubscription;

        if (!$subscription || !$subscription->active()) {
            return response()->json([
                'error' => 'subscription_required',
                'message' => 'Payment required. An active subscription is needed to access this feature.',
                'upgrade_url' => url('/billing/upgrade'),
            ], 402);
        }

        // If a specific tier is requested, check tier clearance
        if ($planTier !== null) {
            $requiredLevel = self::TIER_LEVELS[strtolower($planTier)] ?? 1;
            $currentTier = strtolower($subscription->type ?? 'starter');
            $currentLevel = self::TIER_LEVELS[$currentTier] ?? 1;

            if ($currentLevel < $requiredLevel) {
                return response()->json([
                    'error' => 'plan_upgrade_required',
                    'message' => "This feature requires a '{$planTier}' tier or higher. Current plan: '{$currentTier}'.",
                    'required_tier' => $planTier,
                    'current_tier' => $currentTier,
                    'upgrade_url' => url('/billing/upgrade'),
                ], 403);
            }
        }

        return $next($request);
    }
}
