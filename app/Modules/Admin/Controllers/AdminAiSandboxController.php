<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AiGateway\Services\AiGatewayService;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAiSandboxController extends Controller
{
    public function __construct(
        private readonly AiGatewayService $aiGatewayService
    ) {}

    /**
     * Display the AI Gateway prompt sandbox.
     */
    public function index(): View
    {
        $workspaces = Workspace::select('id', 'name')->orderBy('name')->get();
        $models = [
            'gemini-1.5-pro' => 'Google Gemini 1.5 Pro (Multimodal & Reasoning)',
            'gemini-1.5-flash' => 'Google Gemini 1.5 Flash (Ultra-fast & Lightweight)',
            'gpt-4o' => 'OpenAI GPT-4o (Omni flagship model)',
            'gpt-4o-mini' => 'OpenAI GPT-4o Mini (Cost-efficient)',
            'mock-ai' => 'Local Mock AI (Deterministic testing & CI)',
        ];

        return view('admin.ai.sandbox', compact('workspaces', 'models'));
    }

    /**
     * Run a test inference prompt through the AI Gateway.
     */
    public function run(Request $request): JsonResponse|View
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:4000',
            'model' => 'required|string',
            'workspace_id' => 'nullable|uuid|exists:workspaces,id',
            'temperature' => 'nullable|numeric|min:0|max:2',
        ]);

        $start = microtime(true);

        try {
            // If workspace is provided, execute with full credit metering deduction
            if (!empty($validated['workspace_id'])) {
                $result = $this->aiGatewayService->generate(
                    prompt: $validated['prompt'],
                    model: $validated['model'],
                    workspaceId: $validated['workspace_id'],
                    options: ['temperature' => (float) ($validated['temperature'] ?? 0.7)]
                );
            } else {
                // Admin sandbox direct execution without tenant billing deduction
                $driver = $this->aiGatewayService->resolveDriver($validated['model']);
                $driverResult = $driver->generate(
                    $validated['prompt'],
                    ['model' => $validated['model'], 'temperature' => (float) ($validated['temperature'] ?? 0.7)]
                );

                $totalTokens = $driverResult['total_tokens'] ?? 100;
                $creditsEstimated = max(1, (int) ceil($totalTokens / 500));

                $result = [
                    'content' => $driverResult['content'],
                    'model' => $driverResult['model'],
                    'tokens' => [
                        'prompt_tokens' => $driverResult['prompt_tokens'],
                        'completion_tokens' => $driverResult['completion_tokens'],
                        'total_tokens' => $totalTokens,
                    ],
                    'credits_deducted' => 0,
                    'credits_estimated' => $creditsEstimated,
                    'remaining_credits' => 'N/A (Admin Sandbox)',
                ];
            }

            $latency = round((microtime(true) - $start) * 1000, 2);
            $result['latency_ms'] = $latency;

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                ]);
            }

            return back()->with('ai_result', $result);
        } catch (\Throwable $e) {
            $latency = round((microtime(true) - $start) * 1000, 2);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'latency_ms' => $latency,
                ], 422);
            }

            return back()->with('error', 'AI Execution failed: ' . $e->getMessage());
        }
    }
}
