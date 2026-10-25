<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Auth\Models\User;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Features\Models\FeatureFlag;
use App\Modules\Metering\Models\UsageMeter;
use App\Modules\Monitoring\Services\HealthCheckService;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly HealthCheckService $healthCheckService
    ) {}

    /**
     * Display the Executive Admin Command Center.
     */
    public function index(Request $request): View
    {
        // Core SaaS platform aggregates
        $workspaceCount = Workspace::count();
        $userCount = User::count();
        $superAdminCount = User::where('is_super_admin', true)->count();
        
        // Billing & Revenue metrics
        $activeSubscriptions = Subscription::whereIn('stripe_status', ['active', 'trialing'])->count();
        $estimatedMrr = $activeSubscriptions * 49; // $49/mo Pro default plan

        // Metering & AI Gateway metrics across all tenants
        $totalCreditsAllocated = (int) UsageMeter::withoutGlobalScopes()
            ->where('metric_name', 'ai_credits')
            ->sum('allocated_limit');
        $totalCreditsConsumed = (int) UsageMeter::withoutGlobalScopes()
            ->where('metric_name', 'ai_credits')
            ->sum('current_usage');
        
        $creditUtilizationPercent = $totalCreditsAllocated > 0
            ? round(($totalCreditsConsumed / $totalCreditsAllocated) * 100, 1)
            : 0;

        // Recent Audit logs
        $recentAuditLogs = AuditLog::withoutGlobalScopes()
            ->with(['user', 'workspace'])
            ->latest()
            ->limit(10)
            ->get();

        // System Readiness & Infrastructure health
        $health = $this->healthCheckService->checkReadiness();

        // Active global feature flags count
        $activeFlagsCount = FeatureFlag::whereNull('workspace_id')
            ->where('is_enabled', true)
            ->count();

        return view('admin.dashboard', compact(
            'workspaceCount',
            'userCount',
            'superAdminCount',
            'activeSubscriptions',
            'estimatedMrr',
            'totalCreditsAllocated',
            'totalCreditsConsumed',
            'creditUtilizationPercent',
            'recentAuditLogs',
            'health',
            'activeFlagsCount'
        ));
    }
}
