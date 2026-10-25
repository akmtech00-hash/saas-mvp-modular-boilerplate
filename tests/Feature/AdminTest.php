<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Features\Models\FeatureFlag;
use App\Modules\Metering\Models\UsageMeter;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $regularUser;
    private Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name' => 'SaaS Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'is_super_admin' => true,
        ]);

        $this->regularUser = User::create([
            'name' => 'Regular Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password123'),
            'is_super_admin' => false,
        ]);

        $this->workspace = Workspace::create([
            'name' => 'Acme Corporation',
            'slug' => 'acme-corp',
            'owner_id' => $this->regularUser->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->regularUser->id,
            'role' => 'owner',
        ]);
    }

    public function test_guest_cannot_access_admin_console(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('dashboard'));
    }

    public function test_regular_user_is_forbidden_from_admin_console(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Executive Overview');
        $response->assertSee('Tenant Workspaces');
    }

    public function test_super_admin_can_view_workspaces_directory(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/workspaces');

        $response->assertStatus(200);
        $response->assertSee('Acme Corporation');
        $response->assertSee($this->workspace->slug);
    }

    public function test_super_admin_can_update_workspace_quota(): void
    {
        $response = $this->actingAs($this->superAdmin)->post("/admin/workspaces/{$this->workspace->id}/quota", [
            'metric_name' => 'ai_credits',
            'allocated_limit' => 500,
        ]);

        $response->assertSessionHas('success');

        $meter = UsageMeter::withoutGlobalScopes()
            ->where('workspace_id', $this->workspace->id)
            ->where('metric_name', 'ai_credits')
            ->first();

        $this->assertNotNull($meter);
        $this->assertEquals(500, $meter->allocated_limit);
    }

    public function test_super_admin_can_impersonate_and_stop_impersonating(): void
    {
        // 1. Start impersonation
        $response = $this->actingAs($this->superAdmin)
            ->post("/admin/workspaces/{$this->workspace->id}/impersonate");

        $response->assertRedirect('/');
        $response->assertSessionHas('admin_impersonator_id', $this->superAdmin->id);
        $response->assertSessionHas('impersonated_workspace_id', $this->workspace->id);

        // 2. Visit dashboard with active impersonation banner
        $dashboardResponse = $this->actingAs($this->superAdmin)->get('/');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('ACTIVE TENANT IMPERSONATION');
        $dashboardResponse->assertSee($this->workspace->name);

        // 3. Stop impersonation
        $stopResponse = $this->actingAs($this->superAdmin)
            ->post('/admin/impersonate/stop');

        $stopResponse->assertRedirect('/admin/workspaces');
        $stopResponse->assertSessionMissing('admin_impersonator_id');
    }

    public function test_super_admin_can_toggle_user_super_admin_status(): void
    {
        $this->assertFalse((bool) $this->regularUser->is_super_admin);

        $response = $this->actingAs($this->superAdmin)
            ->post("/admin/users/{$this->regularUser->id}/toggle-admin");

        $response->assertSessionHas('success');
        $this->regularUser->refresh();
        $this->assertTrue((bool) $this->regularUser->is_super_admin);

        // Toggle back
        $response2 = $this->actingAs($this->superAdmin)
            ->post("/admin/users/{$this->regularUser->id}/toggle-admin");

        $response2->assertSessionHas('success');
        $this->regularUser->refresh();
        $this->assertFalse((bool) $this->regularUser->is_super_admin);
    }

    public function test_super_admin_cannot_demote_themselves(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post("/admin/users/{$this->superAdmin->id}/toggle-admin");

        $response->assertSessionHas('error');
        $this->superAdmin->refresh();
        $this->assertTrue((bool) $this->superAdmin->is_super_admin);
    }

    public function test_super_admin_can_toggle_global_feature_flags(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/features/toggle', [
            'feature_key' => 'custom_domains',
            'is_enabled' => 1,
        ]);

        $response->assertSessionHas('success');

        $flag = FeatureFlag::whereNull('workspace_id')
            ->where('feature_key', 'custom_domains')
            ->first();

        $this->assertNotNull($flag);
        $this->assertTrue((bool) $flag->is_enabled);
    }

    public function test_super_admin_can_set_tenant_feature_override(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/features/override', [
            'workspace_id' => $this->workspace->id,
            'feature_key' => 'custom_domains',
            'is_enabled' => 1,
        ]);

        $response->assertSessionHas('success');

        $override = FeatureFlag::where('workspace_id', $this->workspace->id)
            ->where('feature_key', 'custom_domains')
            ->first();

        $this->assertNotNull($override);
        $this->assertTrue((bool) $override->is_enabled);
    }

    public function test_super_admin_can_access_monitoring_cockpit(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/monitoring');

        $response->assertStatus(200);
        $response->assertSee('Health &amp; Monitoring Cockpit', false);
        $response->assertSee('Database Engine');
    }

    public function test_super_admin_can_run_ai_sandbox_inference(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/ai-sandbox/run', [
            'prompt' => 'Hello test LLM',
            'model' => 'mock-ai',
        ]);

        $response->assertSessionHas('ai_result');
    }

    public function test_promote_admin_artisan_command(): void
    {
        $this->assertFalse((bool) $this->regularUser->is_super_admin);

        $this->artisan('saas:promote-admin', ['email' => $this->regularUser->email])
            ->assertSuccessful();

        $this->regularUser->refresh();
        $this->assertTrue((bool) $this->regularUser->is_super_admin);

        // Demote with --demote flag
        $this->artisan('saas:promote-admin', [
            'email' => $this->regularUser->email,
            '--demote' => true,
        ])->assertSuccessful();

        $this->regularUser->refresh();
        $this->assertFalse((bool) $this->regularUser->is_super_admin);
    }
}
