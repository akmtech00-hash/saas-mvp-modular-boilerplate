<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceMember;
use App\Modules\Workspace\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    /**
     * List all workspaces the authenticated user belongs to.
     */
    public function index(Request $request): JsonResponse
    {
        $workspaces = $request->user()
            ->workspaces()
            ->get(['workspaces.id', 'workspaces.name', 'workspaces.slug', 'workspace_members.role', 'workspaces.created_at']);

        return response()->json([
            'data' => $workspaces,
        ]);
    }

    /**
     * Create a new workspace.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $user = $request->user();

        $workspace = DB::transaction(function () use ($validated, $user) {
            $ws = Workspace::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
                'owner_id' => $user->id,
            ]);

            WorkspaceMember::create([
                'workspace_id' => $ws->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);

            return $ws;
        });

        return response()->json([
            'message' => 'Workspace created successfully.',
            'workspace' => $workspace,
        ], 201);
    }

    /**
     * Show details of currently active workspace.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var Workspace $workspace */
        $workspace = app(TenantContext::class)->getWorkspace() ?? $request->attributes->get('current_workspace');

        return response()->json([
            'workspace' => $workspace->load(['activeSubscription', 'usageMeters']),
        ]);
    }

    /**
     * List members of currently active workspace.
     */
    public function members(Request $request): JsonResponse
    {
        /** @var Workspace $workspace */
        $workspace = app(TenantContext::class)->getWorkspace() ?? $request->attributes->get('current_workspace');

        $members = WorkspaceMember::where('workspace_id', $workspace->id)
            ->join('users', 'workspace_members.user_id', '=', 'users.id')
            ->select('workspace_members.id', 'workspace_members.role', 'users.id as user_id', 'users.name', 'users.email', 'workspace_members.created_at')
            ->get();

        return response()->json([
            'workspace_id' => $workspace->id,
            'members' => $members,
        ]);
    }

    /**
     * Add / Invite a member to the workspace.
     */
    public function invite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:admin,member,viewer',
        ]);

        /** @var Workspace $workspace */
        $workspace = app(TenantContext::class)->getWorkspace() ?? $request->attributes->get('current_workspace');

        $invitedUser = User::where('email', $validated['email'])->first();
        if (!$invitedUser) {
            return response()->json([
                'error' => 'user_not_found',
                'message' => 'User with this email does not exist in the platform.',
            ], 404);
        }

        $existing = WorkspaceMember::where('workspace_id', $workspace->id)
            ->where('user_id', $invitedUser->id)
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'already_member',
                'message' => 'User is already a member of this workspace.',
            ], 422);
        }

        $member = WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id' => $invitedUser->id,
            'role' => $validated['role'],
        ]);

        return response()->json([
            'message' => 'Member added successfully.',
            'member' => $member,
        ], 201);
    }
}
