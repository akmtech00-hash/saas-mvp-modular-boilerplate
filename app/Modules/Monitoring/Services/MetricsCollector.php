<?php

declare(strict_types=1);

namespace App\Modules\Monitoring\Services;

use App\Modules\Workspace\Models\Workspace;
use Illuminate\Support\Facades\Cache;

class MetricsCollector
{
    private string $namespace;

    public function __construct()
    {
        $this->namespace = config('monitoring.prometheus.namespace', 'saas_platform');
    }

    /**
     * Record an incoming HTTP request.
     */
    public function recordHttpRequest(string $method, string $route, int $statusCode, float $durationSeconds): void
    {
        $key = "metric:http_requests:{$method}:{$route}:{$statusCode}";
        Cache::increment($key, 1);

        // Record duration histogram bucket
        $latencyKey = "metric:http_latency:{$method}:{$route}";
        $currentSum = (float) Cache::get("{$latencyKey}:sum", 0.0);
        $currentCount = (int) Cache::get("{$latencyKey}:count", 0);

        Cache::put("{$latencyKey}:sum", $currentSum + $durationSeconds, 86400);
        Cache::put("{$latencyKey}:count", $currentCount + 1, 86400);
    }

    /**
     * Track an exception occurrence.
     */
    public function incrementExceptionCount(string $exceptionClass): void
    {
        $cleanClass = str_replace('\\', '_', $exceptionClass);
        Cache::increment("metric:exceptions:{$cleanClass}", 1);
    }

    /**
     * Track AI credit consumption.
     */
    public function recordCreditConsumed(string $metric, string $operation, int $amount): void
    {
        $key = "metric:credits_consumed:{$metric}:{$operation}";
        Cache::increment($key, $amount);
    }

    /**
     * Export all metrics in Prometheus text format (version 0.0.4).
     */
    public function exportPrometheus(): string
    {
        $lines = [];

        // 1. System Memory & Uptime
        $lines[] = "# HELP {$this->namespace}_php_memory_bytes Current PHP memory usage in bytes.";
        $lines[] = "# TYPE {$this->namespace}_php_memory_bytes gauge";
        $lines[] = "{$this->namespace}_php_memory_bytes " . memory_get_usage(true);

        $lines[] = "# HELP {$this->namespace}_php_memory_peak_bytes Peak PHP memory usage in bytes.";
        $lines[] = "# TYPE {$this->namespace}_php_memory_peak_bytes gauge";
        $lines[] = "{$this->namespace}_php_memory_peak_bytes " . memory_get_peak_usage(true);

        // 2. Active SaaS Tenants
        $workspaceCount = 0;
        try {
            $workspaceCount = Workspace::count();
        } catch (\Throwable $e) {
            // DB may not be migrated yet
        }
        $lines[] = "# HELP {$this->namespace}_workspaces_total Total number of registered SaaS workspaces.";
        $lines[] = "# TYPE {$this->namespace}_workspaces_total gauge";
        $lines[] = "{$this->namespace}_workspaces_total {$workspaceCount}";

        // 3. HTTP Request Metrics
        $lines[] = "# HELP {$this->namespace}_http_requests_total Total number of HTTP requests processed.";
        $lines[] = "# TYPE {$this->namespace}_http_requests_total counter";
        $totalRequests = (int) Cache::get('metric:http_requests:total', 0);
        $lines[] = "{$this->namespace}_http_requests_total {$totalRequests}";

        // 4. AI Credits Consumed
        $lines[] = "# HELP {$this->namespace}_ai_credits_consumed_total Total AI credits consumed by tenants.";
        $lines[] = "# TYPE {$this->namespace}_ai_credits_consumed_total counter";
        $totalCredits = (int) Cache::get('metric:credits:total', 0);
        $lines[] = "{$this->namespace}_ai_credits_consumed_total {$totalCredits}";

        return implode("\n", $lines) . "\n";
    }
}
