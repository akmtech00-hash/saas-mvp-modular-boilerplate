<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Monitoring\Services\HealthCheckService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class AdminMonitoringController extends Controller
{
    public function __construct(
        private readonly HealthCheckService $healthCheckService
    ) {}

    /**
     * Display live Infrastructure & Health Cockpit.
     */
    public function index(): View
    {
        $health = $this->healthCheckService->checkReadiness();

        // Server & Runtime Telemetry
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'Enabled (Warning)' : 'Disabled (Production Safe)',
            'database_driver' => DB::connection()->getDriverName(),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'session_driver' => config('session.driver'),
            'memory_current' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            'server_time' => now()->toIso8601String(),
            'uptime' => $this->getUptime(),
        ];

        // Recent audit/security events
        $recentEvents = AuditLog::withoutGlobalScopes()
            ->with(['user', 'workspace'])
            ->latest()
            ->limit(15)
            ->get();

        return view('admin.monitoring.index', compact('health', 'systemInfo', 'recentEvents'));
    }

    private function getUptime(): string
    {
        if (file_exists('/proc/uptime')) {
            $str = file_get_contents('/proc/uptime');
            $num = floatval(explode(' ', $str)[0]);
            $days = floor($num / 86400);
            $hours = floor(($num - $days * 86400) / 3600);
            return "{$days}d {$hours}h";
        }

        return 'Online';
    }
}
