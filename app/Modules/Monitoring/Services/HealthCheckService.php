<?php

declare(strict_types=1);

namespace App\Modules\Monitoring\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthCheckService
{
    /**
     * Run all system readiness checks.
     *
     * @return array{status: string, timestamp: string, checks: array<string, array{status: string, latency_ms?: float, error?: string}>}
     */
    public function checkReadiness(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
            'disk_space' => $this->checkDiskSpace(),
        ];

        $allOk = true;
        foreach ($checks as $check) {
            if ($check['status'] !== 'ok') {
                $allOk = false;
                break;
            }
        }

        return [
            'status' => $allOk ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'app_env' => config('app.env'),
            'checks' => $checks,
        ];
    }

    /**
     * Liveness check (shallow ping to confirm web server/FPM is alive).
     */
    public function checkLiveness(): array
    {
        return [
            'status' => 'alive',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    private function checkDatabase(): array
    {
        $start = microtime(true);
        try {
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000, 2);
            return [
                'status' => 'ok',
                'latency_ms' => $latency,
                'connection' => config('database.default'),
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkRedis(): array
    {
        $start = microtime(true);
        try {
            Redis::ping();
            $latency = round((microtime(true) - $start) * 1000, 2);
            return [
                'status' => 'ok',
                'latency_ms' => $latency,
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $testFile = 'healthcheck_probe_' . time() . '.tmp';
            Storage::disk('local')->put($testFile, 'healthcheck');
            $contents = Storage::disk('local')->get($testFile);
            Storage::disk('local')->delete($testFile);

            if ($contents === 'healthcheck') {
                return ['status' => 'ok'];
            }

            return ['status' => 'failed', 'error' => 'Storage read mismatch'];
        } catch (Throwable $e) {
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    private function checkDiskSpace(): array
    {
        try {
            $freeBytes = disk_free_space(storage_path());
            $totalBytes = disk_total_space(storage_path());

            if ($freeBytes === false || $totalBytes === false) {
                return ['status' => 'ok', 'note' => 'Disk metrics unavailable on current OS'];
            }

            $freeMb = round($freeBytes / 1024 / 1024, 2);
            $minMb = (int) config('monitoring.health.disk_space_min_mb', 512);

            return [
                'status' => $freeMb >= $minMb ? 'ok' : 'degraded',
                'free_mb' => $freeMb,
                'total_mb' => round($totalBytes / 1024 / 1024, 2),
                'percent_free' => round(($freeBytes / $totalBytes) * 100, 1),
            ];
        } catch (Throwable $e) {
            return ['status' => 'degraded', 'error' => $e->getMessage()];
        }
    }
}
