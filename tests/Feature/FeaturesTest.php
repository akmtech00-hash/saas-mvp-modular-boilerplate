<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Features\Models\FeatureFlag;
use App\Modules\Features\Services\FeatureFlagService;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_service_detects_tier_entitlements(): void
    {
        $user = User::create([
            'name' => 'Pro User',
            'email' => 'pro@example.com',
            'password' => bcrypt('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'Pro Workspace',
            'slug' => 'pro-ws',
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
            'stripe_id' => 'sub_features_test_1',
            'stripe_status' => 'active',
            'stripe_price' => 'price_pro_test',
        ]);

        $service = app(FeatureFlagService::class);

        // Pro tier includes 'api_access' => true in config/billing.php
        $this->assertTrue($service->isEnabled('api_access', $workspace->id));
    }

    public function test_tenant_override_takes_precedence_over_plan(): void
    {
        $user = User::create([
            'name' => 'Starter User',
            'email' => 'starter@example.com',
            'password' => bcrypt('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'Starter Workspace',
            'slug' => 'starter-ws',
            'owner_id' => $user->id,
        ]);

        // Explicit tenant override enabling beta feature
        FeatureFlag::create([
            'workspace_id' => $workspace->id,
            'feature_key' => 'custom_domain',
            'is_enabled' => true,
        ]);

        $service = app(FeatureFlagService::class);
        $this->assertTrue($service->isEnabled('custom_domain', $workspace->id));
    }

    public function test_feature_check_endpoint_returns_json_state(): void
    {
        $user = User::create([
            'name' => 'API Tester',
            'email' => 'tester@example.com',
            'password' => bcrypt('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'Flag Workspace',
            'slug' => 'flag-ws',
            'owner_id' => $user->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Workspace-Id', $workspace->id)
            ->getJson('/api/v1/features/check?feature=vector_search');

        $response->assertStatus(200);
        $response->assertJson([
            'feature' => 'vector_search',
            'workspace_id' => $workspace->id,
        ]);
    }
}
