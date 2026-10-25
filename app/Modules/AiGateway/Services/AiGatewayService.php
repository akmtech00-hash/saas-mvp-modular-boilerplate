<?php

declare(strict_types=1);

namespace App\Modules\AiGateway\Services;

use App\Modules\AiGateway\Contracts\AiProviderInterface;
use App\Modules\AiGateway\Drivers\GeminiDriver;
use App\Modules\AiGateway\Drivers\MockAiDriver;
use App\Modules\AiGateway\Drivers\OpenAiDriver;
use App\Modules\Metering\Services\CreditMeterService;
use App\Modules\Workspace\Services\TenantContext;

class AiGatewayService
{
    public function __construct(
        private readonly CreditMeterService $meterService
    ) {}

    /**
     * Generate text through unified LLM gateway with automated credit deduction.
     */
    public function generate(string $prompt, string $model = 'gemini-1.5-pro', ?string $workspaceId = null, array $options = []): array
    {
        $resolvedWorkspaceId = $workspaceId ?? (string) app(TenantContext::class)->getWorkspaceId();

        // 1. Pre-flight check: ensure tenant has at least 1 credit available
        if (!$this->meterService->hasCredits($resolvedWorkspaceId, 1)) {
            throw new \App\Modules\Metering\Exceptions\InsufficientCreditsException('ai_credits', 1, 0);
        }

        // 2. Select driver based on model name
        $driver = $this->resolveDriver($model);

        // 3. Execute generation
        $result = $driver->generate($prompt, array_merge($options, ['model' => $model]));

        // 4. Calculate credit cost: 1 credit per 500 total tokens (min 1)
        $totalTokens = $result['total_tokens'] ?? 100;
        $creditsToDeduct = max(1, (int) ceil($totalTokens / 500));

        // 5. Atomically deduct calculated credits
        $updatedMeter = $this->meterService->deductCredits(
            workspaceId: $resolvedWorkspaceId,
            credits: $creditsToDeduct,
            operation: "ai_llm_query:{$model}",
            userId: auth()->id(),
            metadata: [
                'model' => $model,
                'prompt_tokens' => $result['prompt_tokens'],
                'completion_tokens' => $result['completion_tokens'],
                'total_tokens' => $totalTokens,
            ]
        );

        return [
            'content' => $result['content'],
            'model' => $result['model'],
            'tokens' => [
                'prompt_tokens' => $result['prompt_tokens'],
                'completion_tokens' => $result['completion_tokens'],
                'total_tokens' => $totalTokens,
            ],
            'credits_deducted' => $creditsToDeduct,
            'remaining_credits' => $updatedMeter->remainingCredits(),
        ];
    }

    /**
     * Resolve provider driver for given model.
     */
    public function resolveDriver(string $model): AiProviderInterface
    {
        if (str_starts_with($model, 'gemini-')) {
            return new GeminiDriver();
        }

        if (str_starts_with($model, 'gpt-') || str_starts_with($model, 'o1-')) {
            return new OpenAiDriver();
        }

        return new MockAiDriver();
    }
}
