<?php

declare(strict_types=1);

namespace App\Modules\AiGateway\Controllers;

use App\Modules\AiGateway\Services\AiGatewayService;
use App\Modules\Metering\Exceptions\InsufficientCreditsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AiGatewayController extends Controller
{
    public function __construct(
        private readonly AiGatewayService $gatewayService
    ) {}

    /**
     * Generate text completion using AI Gateway.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:10000',
            'model' => 'nullable|string|max:50',
        ]);

        $model = $validated['model'] ?? 'gemini-1.5-pro';

        try {
            $response = $this->gatewayService->generate(
                prompt: $validated['prompt'],
                model: $model
            );

            return response()->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (InsufficientCreditsException $e) {
            return response()->json([
                'success' => false,
                'error' => 'credit_exhausted',
                'message' => $e->getMessage(),
                'upgrade_url' => url('/billing/upgrade'),
            ], 429);
        }
    }

    /**
     * List supported models and token pricing.
     */
    public function models(): JsonResponse
    {
        return response()->json([
            'models' => [
                [
                    'id' => 'gemini-1.5-pro',
                    'provider' => 'Google',
                    'context_window' => 1000000,
                    'cost_credits_per_500_tokens' => 1,
                ],
                [
                    'id' => 'gpt-4o-mini',
                    'provider' => 'OpenAI',
                    'context_window' => 128000,
                    'cost_credits_per_500_tokens' => 1,
                ],
                [
                    'id' => 'mock-ai-v1',
                    'provider' => 'Simulation',
                    'context_window' => 8000,
                    'cost_credits_per_500_tokens' => 1,
                ],
            ]
        ]);
    }
}
