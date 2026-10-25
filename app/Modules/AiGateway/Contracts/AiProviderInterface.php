<?php

declare(strict_types=1);

namespace App\Modules\AiGateway\Contracts;

interface AiProviderInterface
{
    /**
     * Generate text completion and return unified token usage statistics.
     *
     * @return array{content: string, model: string, prompt_tokens: int, completion_tokens: int, total_tokens: int}
     */
    public function generate(string $prompt, array $options = []): array;
}
