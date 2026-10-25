<?php

declare(strict_types=1);

namespace App\Modules\Features\Controllers;

use App\Modules\Features\Services\FeatureFlagService;
use App\Modules\Workspace\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FeatureController extends Controller
{
    public function __construct(
        private readonly FeatureFlagService $featureService
    ) {}

    /**
     * Check if a specific feature flag is active for current workspace.
     */
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'feature' => 'required|string|max:100',
        ]);

        $workspaceId = app(TenantContext::class)->getWorkspaceId();
        $isEnabled = $this->featureService->isEnabled($validated['feature'], $workspaceId);

        return response()->json([
            'feature' => $validated['feature'],
            'enabled' => $isEnabled,
            'workspace_id' => $workspaceId,
        ]);
    }
}
