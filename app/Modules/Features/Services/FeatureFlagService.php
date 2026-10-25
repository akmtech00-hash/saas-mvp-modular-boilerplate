<?php

declare(strict_types=1);

namespace App\Modules\Features\Services;

use App\Modules\Features\Models\FeatureFlag;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Services\TenantContext;

class FeatureFlagService
{
    /**
     * Determine if a feature flag is enabled for the given (or active) workspace.
     */
    public function isEnabled(string $featureKey, ?string $workspaceId = null): bool
    {
        $resolvedWorkspaceId = $workspaceId ?? app(TenantContext::class)->getWorkspaceId();

        // 1. Check workspace-specific override in database
        if ($resolvedWorkspaceId) {
            $tenantOverride = FeatureFlag::where('workspace_id', $resolvedWorkspaceId)
                ->where('feature_key', $featureKey)
                ->first();

            if ($tenantOverride !== null) {
                return (bool) $tenantOverride->is_enabled;
            }

            // 2. Check tenant's billing subscription plan features
            $workspace = app(TenantContext::class)->getWorkspace() ?? Workspace::find($resolvedWorkspaceId);
            if ($workspace && $workspace->activeSubscription) {
                $tier = strtolower($workspace->activeSubscription->type ?? 'starter');
                $planFeatures = config("billing.plans.{$tier}.features", []);

                if (array_key_exists($featureKey, $planFeatures)) {
                    return (bool) $planFeatures[$featureKey];
                }
            }
        }

        // 3. Check global feature flag override
        $globalFlag = FeatureFlag::whereNull('workspace_id')
            ->where('feature_key', $featureKey)
            ->first();

        if ($globalFlag !== null) {
            return (bool) $globalFlag->is_enabled;
        }

        // 4. Default fallback based on config/features.php if available, else false
        return (bool) config("features.defaults.{$featureKey}", false);
    }

    /**
     * Set a workspace-specific feature flag override.
     */
    public function setWorkspaceOverride(string $workspaceId, string $featureKey, bool $enabled, array $rules = []): FeatureFlag
    {
        return FeatureFlag::updateOrCreate(
            ['workspace_id' => $workspaceId, 'feature_key' => $featureKey],
            ['is_enabled' => $enabled, 'rules' => $rules]
        );
    }
}
