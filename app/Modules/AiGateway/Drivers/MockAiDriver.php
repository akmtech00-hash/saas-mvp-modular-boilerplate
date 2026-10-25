<?php

declare(strict_types=1);

namespace App\Modules\AiGateway\Drivers;

use App\Modules\AiGateway\Contracts\AiProviderInterface;

class MockAiDriver implements AiProviderInterface
{
    public function generate(string $prompt, array $options = []): array
    {
        $promptTokens = max(1, (int) ceil(strlen($prompt) / 4));
        $simulatedResponse = "Simulated AI response for prompt: " . substr($prompt, 0, 50) . "...";
        $completionTokens = max(1, (int) ceil(strlen($simulatedResponse) / 4));

        return [
            'content' => $simulatedResponse,
            'model' => $options['model'] ?? 'mock-ai-v1',
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
        ];
    }
}
