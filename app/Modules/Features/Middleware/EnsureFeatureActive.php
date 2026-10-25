<?php

declare(strict_types=1);

namespace App\Modules\Features\Middleware;

use App\Modules\Features\Services\FeatureFlagService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureActive
{
    public function __construct(
        private readonly FeatureFlagService $featureService
    ) {}

    /**
     * Handle incoming request and verify required feature flag is enabled.
     */
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        if (!$this->featureService->isEnabled($featureKey)) {
            return response()->json([
                'error' => 'feature_not_available',
                'message' => "The feature '{$featureKey}' is not enabled for your workspace. Upgrade your plan to access it.",
                'required_feature' => $featureKey,
                'upgrade_url' => url('/billing/upgrade'),
            ], 403);
        }

        return $next($request);
    }
}
