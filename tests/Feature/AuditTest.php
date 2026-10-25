<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Audit\Services\AuditLogger;
use App\Modules\Auth\Models\User;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logger_persists_action(): void
    {
        $user = User::create([
            'name' => 'Auditor',
            'email' => 'auditor@example.com',
            'password' => bcrypt('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'Audit Workspace',
            'slug' => 'audit-ws',
            'owner_id' => $user->id,
        ]);

        $logger = app(AuditLogger::class);
        $entry = $logger->log(
            event: 'plan.upgraded',
            newValues: ['tier' => 'pro'],
            oldValues: ['tier' => 'starter'],
            workspaceId: $workspace->id,
            userId: $user->id
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $entry->id,
            'workspace_id' => $workspace->id,
            'event' => 'plan.upgraded',
        ]);
    }

    public function test_owner_can_fetch_audit_logs(): void
    {
        $owner = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'Compliance Workspace',
            'slug' => 'compliance-ws',
            'owner_id' => $owner->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'role' => 'owner',
        ]);

        $logger = app(AuditLogger::class);
        $logger->log('member.invited', ['email' => 'bob@example.com'], workspaceId: $workspace->id, userId: $owner->id);

        $response = $this->actingAs($owner, 'sanctum')
            ->withHeader('X-Workspace-Id', $workspace->id)
            ->getJson('/api/v1/audit/logs');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'event', 'new_values', 'created_at']
            ]
        ]);
    }
}
