<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Features\Models\FeatureFlag;
use App\Modules\Features\Services\FeatureFlagService;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminFeatureFlagController extends Controller
{
    public function __construct(
        private readonly FeatureFlagService $featureFlagService
    ) {}

    /**
     * Display global feature flags and tenant-level overrides.
     */
    public function index(): View
    {
        $defaultFeatures = config('features.defaults', [
            'api_access' => true,
            'webhooks' => true,
            'ai_gateway' => true,
            'audit_logs' => true,
            'team_invitations' => true,
            'custom_domains' => false,
            'advanced_analytics' => false,
        ]);

        $globalOverrides = FeatureFlag::whereNull('workspace_id')
            ->get()
            ->keyBy('feature_key');

        $activeFlags = [];
        foreach ($defaultFeatures as $key => $defaultVal) {
            $isEnabled = isset($globalOverrides[$key])
                ? (bool) $globalOverrides[$key]->is_enabled
                : (bool) $defaultVal;

            $activeFlags[$key] = [
                'key' => $key,
                'is_enabled' => $isEnabled,
                'is_overridden' => isset($globalOverrides[$key]),
                'description' => $this->getFeatureDescription($key),
            ];
        }

        $tenantOverrides = FeatureFlag::whereNotNull('workspace_id')
            ->with('workspace')
            ->latest()
            ->limit(50)
            ->get();

        $workspaces = Workspace::select('id', 'name')->orderBy('name')->get();

        return view('admin.features.index', compact('activeFlags', 'tenantOverrides', 'workspaces'));
    }

    /**
     * Toggle a global feature flag on/off.
     */
    public function toggleGlobal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'feature_key' => 'required|string|max:64',
            'is_enabled' => 'required|boolean',
        ]);

        $flag = FeatureFlag::updateOrCreate(
            ['workspace_id' => null, 'feature_key' => $validated['feature_key']],
            ['is_enabled' => (bool) $validated['is_enabled']]
        );

        AuditLog::withoutGlobalScopes()->create([
            'workspace_id' => null,
            'user_id' => auth()->id(),
            'event' => 'admin.feature_flag_toggled',
            'auditable_type' => FeatureFlag::class,
            'auditable_id' => (string) $flag->id,
            'old_values' => [],
            'new_values' => ['feature_key' => $flag->feature_key, 'is_enabled' => $flag->is_enabled],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $state = $flag->is_enabled ? 'enabled' : 'disabled';
        return back()->with('success', "Global feature flag '{$flag->feature_key}' successfully {$state}.");
    }

    /**
     * Set a tenant-specific feature override.
     */
    public function setOverride(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'workspace_id' => 'required|uuid|exists:workspaces,id',
            'feature_key' => 'required|string|max:64',
            'is_enabled' => 'required|boolean',
        ]);

        $flag = $this->featureFlagService->setWorkspaceOverride(
            $validated['workspace_id'],
            $validated['feature_key'],
            (bool) $validated['is_enabled']
        );

        AuditLog::withoutGlobalScopes()->create([
            'workspace_id' => $validated['workspace_id'],
            'user_id' => auth()->id(),
            'event' => 'admin.tenant_feature_override',
            'auditable_type' => FeatureFlag::class,
            'auditable_id' => (string) $flag->id,
            'old_values' => [],
            'new_values' => ['feature_key' => $flag->feature_key, 'is_enabled' => $flag->is_enabled],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Tenant feature override applied for '{$flag->feature_key}'.");
    }

    /**
     * Remove a tenant override to revert to default/plan setting.
     */
    public function deleteOverride(Request $request, int $id): RedirectResponse
    {
        $flag = FeatureFlag::whereNotNull('workspace_id')->findOrFail($id);
        $key = $flag->feature_key;
        $flag->delete();

        return back()->with('success', "Tenant override for '{$key}' removed.");
    }

    private function getFeatureDescription(string $key): string
    {
        return match ($key) {
            'api_access' => 'REST API and Personal Access Token (Sanctum) endpoints access.',
            'webhooks' => 'Outbound signed HMAC webhook delivery pipeline.',
            'ai_gateway' => 'Unified AI LLM routing to Gemini and OpenAI with credit metering.',
            'audit_logs' => 'Immutable audit logging for compliance and security forensics.',
            'team_invitations' => 'Multi-member invitations with cryptographic signature verification.',
            'custom_domains' => 'Enterprise custom CNAME domain binding and TLS provisioning.',
            'advanced_analytics' => 'High-resolution telemetry graphs and Prometheus export.',
            default => 'Platform capability toggle.',
        };
    }
}
