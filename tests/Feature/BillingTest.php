<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_plans_endpoint_returns_configured_pricing_tiers(): void
    {
        $response = $this->getJson('/api/v1/billing/plans');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'plans' => [
                'starter' => ['name', 'price_usd', 'features'],
                'pro' => ['name', 'price_usd', 'features'],
                'enterprise' => ['name', 'price_usd', 'features'],
            ],
        ]);
    }

    public function test_workspace_billing_status_reflects_active_subscription(): void
    {
        $user = User::create([
            'name' => 'Subscriber',
            'email' => 'sub@example.com',
            'password' => bcrypt('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'SaaS Company',
            'slug' => 'saas-company',
            'owner_id' => $user->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        Subscription::create([
            'workspace_id' => $workspace->id,
            'type' => 'pro',
            'stripe_id' => 'sub_test_pro_123',
            'stripe_status' => 'active',
            'stripe_price' => 'price_pro_test',
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Workspace-Id', $workspace->id)
            ->getJson('/api/v1/billing/status');

        $response->assertStatus(200);
        $response->assertJson([
            'has_active_subscription' => true,
            'subscription' => [
                'type' => 'pro',
                'stripe_status' => 'active',
            ],
        ]);
    }
}
