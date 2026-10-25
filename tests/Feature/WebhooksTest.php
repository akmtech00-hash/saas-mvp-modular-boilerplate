<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Webhooks\Models\WebhookEndpoint;
use App\Modules\Webhooks\Services\WebhookDispatcherService;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhooksTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_webhook_endpoint_and_receive_secret(): void
    {
        $user = User::create([
            'name' => 'Webhook User',
            'email' => 'webhook@example.com',
            'password' => bcrypt('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'Webhook Workspace',
            'slug' => 'webhook-ws',
            'owner_id' => $user->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Workspace-Id', $workspace->id)
            ->postJson('/api/v1/webhooks', [
                'url' => 'https://api.customer.com/webhooks',
                'events' => ['member.invited', 'subscription.renewed'],
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'endpoint' => ['id', 'url', 'events'],
            'signing_secret',
        ]);

        $this->assertDatabaseHas('webhook_endpoints', [
            'workspace_id' => $workspace->id,
            'url' => 'https://api.customer.com/webhooks',
        ]);
    }

    public function test_dispatcher_service_records_signed_delivery(): void
    {
        Http::fake([
            'https://api.customer.com/*' => Http::response(['received' => true], 200),
        ]);

        $workspace = Workspace::create([
            'name' => 'Dispatch Workspace',
            'slug' => 'dispatch-ws',
            'owner_id' => 1,
        ]);

        $endpoint = WebhookEndpoint::withoutGlobalScopes()->create([
            'workspace_id' => $workspace->id,
            'url' => 'https://api.customer.com/endpoint',
            'secret' => 'whsec_test_secret_12345',
            'events' => ['test.event'],
            'is_active' => true,
        ]);

        $dispatcher = app(WebhookDispatcherService::class);
        $delivery = $dispatcher->deliver($endpoint, 'test.event', ['test' => 'data']);

        $this->assertEquals('success', $delivery->status);
        $this->assertEquals(200, $delivery->response_status);

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-SaaS-Signature-256') &&
                   $request->header('X-SaaS-Event')[0] === 'test.event';
        });
    }
}
