<?php

declare(strict_types=1);

namespace App\Modules\Billing\Controllers;

use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BillingController extends Controller
{
    /**
     * List all available SaaS pricing tiers and features.
     */
    public function plans(): JsonResponse
    {
        return response()->json([
            'plans' => config('billing.plans'),
        ]);
    }

    /**
     * Get active subscription details for current workspace.
     */
    public function status(Request $request): JsonResponse
    {
        /** @var Workspace $workspace */
        $workspace = app(TenantContext::class)->getWorkspace() ?? $request->attributes->get('current_workspace');

        $subscription = $workspace->activeSubscription;

        return response()->json([
            'workspace_id' => $workspace->id,
            'has_active_subscription' => $subscription !== null && $subscription->active(),
            'subscription' => $subscription,
            'plans' => config('billing.plans'),
        ]);
    }
}
