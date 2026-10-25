<?php

declare(strict_types=1);

namespace App\Modules\AiGateway\Drivers;

use App\Modules\AiGateway\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiDriver implements AiProviderInterface
{
    private string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? (string) env('OPENAI_API_KEY', '');
    }

    public function generate(string $prompt, array $options = []): array
    {
        if (empty($this->apiKey)) {
            // Fallback to mock driver if key not provided
            return (new MockAiDriver())->generate($prompt, ['model' => 'gpt-4o-mini-simulated']);
        }

        $model = $options['model'] ?? 'gpt-4o-mini';

        $response = Http::timeout(30)
            ->withToken($this->apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException("OpenAI API error: " . $response->body());
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content'] ?? '';
        $usage = $data['usage'] ?? [];

        return [
            'content' => $text,
            'model' => $model,
            'prompt_tokens' => $usage['prompt_tokens'] ?? 10,
            'completion_tokens' => $usage['completion_tokens'] ?? 20,
            'total_tokens' => $usage['total_tokens'] ?? 30,
        ];
    }
}
