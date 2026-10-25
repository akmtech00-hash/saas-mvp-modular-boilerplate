<?php

declare(strict_types=1);

namespace App\Modules\Metering\Controllers;

use App\Modules\Metering\Exceptions\InsufficientCreditsException;
use App\Modules\Metering\Models\UsageMeter;
use App\Modules\Metering\Services\CreditMeterService;
use App\Modules\Workspace\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class CreditMeterController extends Controller
{
    /**
     * Get credit quota and consumption status for current workspace.
     */
    public function status(Request $request): JsonResponse
    {
        $workspaceId = app(TenantContext::class)->getWorkspaceId() 
            ?? $request->header('X-Workspace-Id') 
            ?? $request->query('workspace_id');

        if (!$workspaceId || !Str::isUuid((string) $workspaceId)) {
            return response()->json([
                'error' => 'invalid_workspace_id',
                'message' => 'Valid UUID Workspace ID required in X-Workspace-Id header.',
            ], 400);
        }

        $meter = UsageMeter::withoutGlobalScopes()->firstOrCreate(
            ['workspace_id' => $workspaceId, 'metric_name' => 'ai_credits'],
            ['allocated_limit' => 100, 'current_usage' => 0, 'cycle_resets_at' => now()->addMonth()]
        );

        return response()->json([
            'workspace_id' => $workspaceId,
            'metric' => 'ai_credits',
            'allocated_limit' => $meter->allocated_limit,
            'current_usage' => $meter->current_usage,
            'remaining_credits' => $meter->remainingCredits(),
            'cycle_resets_at' => $meter->cycle_resets_at?->toIso8601String(),
        ]);
    }

    /**
     * Deduct credits for an AI agent query or operation.
     */
    public function deduct(Request $request, CreditMeterService $meterService): JsonResponse
    {
        $workspaceId = app(TenantContext::class)->getWorkspaceId() 
            ?? $request->input('workspace_id')
            ?? $request->header('X-Workspace-Id');

        $validated = $request->validate([
            'amount' => 'required|integer|min:1|max:1000',
            'operation' => 'required|string|max:100',
        ]);

        if (!$workspaceId || !Str::isUuid((string) $workspaceId)) {
            return response()->json([
                'error' => 'invalid_workspace_id',
                'message' => 'Valid UUID Workspace ID required.',
            ], 400);
        }

        try {
            $updatedMeter = $meterService->deductCredits(
                workspaceId: (string) $workspaceId,
                credits: $validated['amount'],
                operation: $validated['operation'],
                userId: $request->user()?->id
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully deducted {$validated['amount']} credits.",
                'remaining_credits' => $updatedMeter->remainingCredits(),
                'current_usage' => $updatedMeter->current_usage,
                'allocated_limit' => $updatedMeter->allocated_limit,
            ]);
        } catch (InsufficientCreditsException $e) {
            return response()->json([
                'success' => false,
                'error' => 'credit_limit_exceeded',
                'message' => $e->getMessage(),
            ], 429);
        }
    }
}
