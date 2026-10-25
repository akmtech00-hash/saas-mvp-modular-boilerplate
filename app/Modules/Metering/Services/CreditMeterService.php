<?php

declare(strict_types=1);

namespace App\Modules\Metering\Services;

use App\Modules\Metering\Exceptions\InsufficientCreditsException;
use App\Modules\Metering\Models\UsageLog;
use App\Modules\Metering\Models\UsageMeter;
use App\Modules\Monitoring\Services\MetricsCollector;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreditMeterService
{
    /**
     * Check if a workspace has enough credits for a planned operation.
     */
    public function hasCredits(string $workspaceId, int $requiredCredits = 1, string $metric = 'ai_credits'): bool
    {
        if ($requiredCredits < 1) {
            throw new InvalidArgumentException('Required credits must be greater than zero.');
        }

        $meter = UsageMeter::withoutGlobalScopes()->firstOrCreate(
            ['workspace_id' => $workspaceId, 'metric_name' => $metric],
            ['allocated_limit' => 100, 'current_usage' => 0, 'cycle_resets_at' => now()->addMonth()]
        );

        $this->maybeResetCycle($meter);

        return $meter->remainingCredits() >= $requiredCredits;
    }

    /**
     * Atomically deduct credits, record an audit log, and update observability metrics.
     *
     * @throws InsufficientCreditsException
     */
    public function deductCredits(
        string $workspaceId,
        int $credits = 1,
        string $operation = 'llm_query',
        ?int $userId = null,
        array $metadata = [],
        string $metric = 'ai_credits'
    ): UsageMeter {
        if ($credits < 1) {
            throw new InvalidArgumentException('Deduction amount must be at least 1 credit.');
        }

        $meter = DB::transaction(function () use ($workspaceId, $credits, $operation, $userId, $metadata, $metric) {
            /** @var UsageMeter $meter */
            $meter = UsageMeter::withoutGlobalScopes()
                ->where('workspace_id', $workspaceId)
                ->where('metric_name', $metric)
                ->lockForUpdate()
                ->first();

            if (!$meter) {
                $meter = UsageMeter::withoutGlobalScopes()->create([
                    'workspace_id' => $workspaceId,
                    'metric_name' => $metric,
                    'allocated_limit' => 100,
                    'current_usage' => 0,
                    'cycle_resets_at' => now()->addMonth(),
                ]);
            }

            $this->maybeResetCycle($meter);

            if ($meter->remainingCredits() < $credits) {
                throw new InsufficientCreditsException($metric, $credits, $meter->remainingCredits());
            }

            $meter->increment('current_usage', $credits);

            UsageLog::withoutGlobalScopes()->create([
                'workspace_id' => $workspaceId,
                'user_id' => $userId,
                'metric_name' => $metric,
                'amount' => $credits,
                'operation' => $operation,
                'metadata' => $metadata,
            ]);

            return $meter->refresh();
        });

        // Record credit telemetry in Monitoring service
        if (app()->bound('monitoring.metrics')) {
            app('monitoring.metrics')->recordCreditConsumed($metric, $operation, $credits);
        }

        return $meter;
    }

    /**
     * Reset usage counters if monthly billing cycle has expired.
     */
    private function maybeResetCycle(UsageMeter $meter): void
    {
        if ($meter->cycle_resets_at && now()->greaterThanOrEqualTo($meter->cycle_resets_at)) {
            $meter->update([
                'current_usage' => 0,
                'cycle_resets_at' => now()->addMonth(),
            ]);
        }
    }
}
