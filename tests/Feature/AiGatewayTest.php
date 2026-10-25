<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Metering\Models\UsageMeter;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_catalog_endpoint_returns_supported_llms(): void
    {
        $user = User::create([
            'name' => 'AI User',
            'email' => 'ai@example.com',
            'password' => bcrypt('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'AI Workspace',
            'slug' => 'ai-ws',
            'owner_id' => $user->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Workspace-Id', $workspace->id)
            ->getJson('/api/v1/ai/models');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'models' => [
                '*' => ['id', 'provider', 'context_window']
            ]
        ]);
    }

    public function test_ai_generate_deducts_credits_and_returns_content(): void
    {
        $user = User::create([
            'name' => 'Prompt Engineer',
            'email' => 'prompt@example.com',
            'password' => bcrypt('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'Generation Workspace',
            'slug' => 'gen-ws',
            'owner_id' => $user->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        UsageMeter::withoutGlobalScopes()->create([
            'workspace_id' => $workspace->id,
            'metric_name' => 'ai_credits',
            'allocated_limit' => 100,
            'current_usage' => 10,
            'cycle_resets_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Workspace-Id', $workspace->id)
            ->postJson('/api/v1/ai/generate', [
                'prompt' => 'Write a unit test for a Laravel service',
                'model' => 'mock-ai-v1',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'data' => [
                'content',
                'model',
                'tokens' => ['prompt_tokens', 'completion_tokens', 'total_tokens'],
                'credits_deducted',
                'remaining_credits',
            ]
        ]);
    }

    public function test_ai_generate_fails_with_429_when_credits_exhausted(): void
    {
        $user = User::create([
            'name' => 'Exhausted User',
            'email' => 'exhausted@example.com',
            'password' => bcrypt('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'Exhausted Workspace',
            'slug' => 'exhausted-ws',
            'owner_id' => $user->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        UsageMeter::withoutGlobalScopes()->create([
            'workspace_id' => $workspace->id,
            'metric_name' => 'ai_credits',
            'allocated_limit' => 50,
            'current_usage' => 50, // 0 remaining
            'cycle_resets_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Workspace-Id', $workspace->id)
            ->postJson('/api/v1/ai/generate', [
                'prompt' => 'Summarize this article',
                'model' => 'mock-ai-v1',
            ]);

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
            'error' => 'credit_exhausted',
        ]);
    }
}
