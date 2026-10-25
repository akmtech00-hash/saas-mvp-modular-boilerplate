<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Middleware;

use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceAccess
{
    /**
     * Handle incoming request and ensure user belongs to requested workspace.
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $workspaceId = $request->header('X-Workspace-Id') ?? $request->route('workspace_id');

        if (!$workspaceId) {
            return response()->json([
                'error' => 'missing_workspace_header',
                'message' => 'Missing workspace context header: X-Workspace-Id.',
            ], 400);
        }

        // Validate UUID syntax before querying database
        if (!Str::isUuid($workspaceId)) {
            return response()->json([
                'error' => 'invalid_workspace_id',
                'message' => 'The provided X-Workspace-Id is not a valid UUID.',
            ], 400);
        }

        /** @var Workspace|null $workspace */
        $workspace = Workspace::find($workspaceId);
        if (!$workspace) {
            return response()->json([
                'error' => 'workspace_not_found',
                'message' => 'Workspace not found.',
            ], 404);
        }

        $requiredRoles = $role ? explode('|', $role) : ['owner', 'admin', 'member', 'viewer'];

        $hasImpersonationSession = $request->hasSession() && $request->session()->has('admin_impersonator_id');
        $isSuperAdminOrImpersonator = ($user->is_super_admin ?? false) || $hasImpersonationSession;

        if (!$isSuperAdminOrImpersonator && !$workspace->hasUserRole((int) $user->id, $requiredRoles)) {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'Forbidden. Insufficient permissions for this workspace.',
            ], 403);
        }

        // Bind workspace to global TenantContext singleton and request attributes
        app(TenantContext::class)->setWorkspace($workspace);
        $request->attributes->set('current_workspace', $workspace);

        return $next($request);
    }
}
