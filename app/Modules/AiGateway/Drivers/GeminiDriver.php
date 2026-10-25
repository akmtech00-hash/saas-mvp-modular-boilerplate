<?php

declare(strict_types=1);

namespace App\Modules\AiGateway\Drivers;

use App\Modules\AiGateway\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiDriver implements AiProviderInterface
{
    private string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? (string) env('GEMINI_API_KEY', '');
    }

    public function generate(string $prompt, array $options = []): array
    {
        if (empty($this->apiKey)) {
            // Fallback to mock driver if key not provided
            return (new MockAiDriver())->generate($prompt, ['model' => 'gemini-1.5-pro-simulated']);
        }

        $model = $options['model'] ?? 'gemini-1.5-pro';
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout(30)->post($endpoint, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
        ]);

        if (!$response->successful()) {
            throw new RuntimeException("Gemini API error: " . $response->body());
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $usage = $data['usageMetadata'] ?? [];

        return [
            'content' => $text,
            'model' => $model,
            'prompt_tokens' => $usage['promptTokenCount'] ?? 10,
            'completion_tokens' => $usage['candidatesTokenCount'] ?? 20,
            'total_tokens' => $usage['totalTokenCount'] ?? 30,
        ];
    }
}
