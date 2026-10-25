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

        $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'api.workspace.invitation.accept',
            now()->addDays(7),
            [
                'workspace' => $workspace->id,
                'email' => $validated['email'],
                'role' => $validated['role'],
            ]
        );

        $invitedUser = User::where('email', $validated['email'])->first();
        if ($invitedUser) {
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
                'invitation_url' => $signedUrl,
                'expires_at' => now()->addDays(7)->toIso8601String(),
            ], 201);
        }

        return response()->json([
            'message' => 'Invitation link generated. Send this link to the user to join.',
            'invitation_url' => $signedUrl,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'expires_at' => now()->addDays(7)->toIso8601String(),
        ], 201);
    }

    /**
     * Accept a signed invitation URL.
     */
    public function acceptInvitation(Request $request): JsonResponse
    {
        if (!$request->hasValidSignature()) {
            return response()->json([
                'error' => 'invalid_signature',
                'message' => 'The invitation link is invalid or has expired.',
            ], 403);
        }

        $workspaceId = $request->query('workspace');
        $email = $request->query('email');
        $role = $request->query('role', 'member');

        $user = $request->user();
        if (!$user || $user->email !== $email) {
            return response()->json([
                'error' => 'email_mismatch',
                'message' => "You must be authenticated as {$email} to accept this invitation.",
            ], 403);
        }

        $existing = WorkspaceMember::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You are already a member of this workspace.',
            ]);
        }

        $member = WorkspaceMember::create([
            'workspace_id' => $workspaceId,
            'user_id' => $user->id,
            'role' => $role,
        ]);

        return response()->json([
            'message' => 'Successfully joined workspace!',
            'member' => $member,
        ], 200);
    }
}
