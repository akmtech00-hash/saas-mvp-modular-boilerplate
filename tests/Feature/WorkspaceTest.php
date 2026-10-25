<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_uuid_workspace_header_returns_400(): void
    {
        $user = User::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Workspace-Id', 'not-a-valid-uuid')
            ->getJson('/api/v1/workspace/current');

        $response->assertStatus(400);
        $response->assertJson(['error' => 'invalid_workspace_id']);
    }

    public function test_user_without_workspace_membership_gets_403_forbidden(): void
    {
        $userA = User::create([
            'name' => 'User A',
            'email' => 'userA@example.com',
            'password' => bcrypt('password'),
        ]);

        $userB = User::create([
            'name' => 'User B',
            'email' => 'userB@example.com',
            'password' => bcrypt('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'Workspace A',
            'slug' => 'workspace-a',
            'owner_id' => $userA->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $userA->id,
            'role' => 'owner',
        ]);

        // User B attempts to access Workspace A
        $response = $this->actingAs($userB, 'sanctum')
            ->withHeader('X-Workspace-Id', $workspace->id)
            ->getJson('/api/v1/workspace/current');

        $response->assertStatus(403);
    }

    public function test_workspace_member_can_access_workspace_and_invite(): void
    {
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $colleague = User::create([
            'name' => 'Colleague',
            'email' => 'colleague@example.com',
            'password' => bcrypt('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'Engineering Team',
            'slug' => 'eng-team',
            'owner_id' => $owner->id,
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'role' => 'owner',
        ]);

        // Access current workspace
        $response = $this->actingAs($owner, 'sanctum')
            ->withHeader('X-Workspace-Id', $workspace->id)
            ->getJson('/api/v1/workspace/current');

        $response->assertStatus(200);

        // Invite colleague
        $inviteResponse = $this->actingAs($owner, 'sanctum')
            ->withHeader('X-Workspace-Id', $workspace->id)
            ->postJson('/api/v1/workspace/members/invite', [
                'email' => 'colleague@example.com',
                'role' => 'member',
            ]);

        $inviteResponse->assertStatus(201);
        $this->assertDatabaseHas('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $colleague->id,
            'role' => 'member',
        ]);
    }
}
