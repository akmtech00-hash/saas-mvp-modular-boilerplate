<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new user and initialize their default workspace.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Create initial workspace for the user
            $workspaceName = $validated['workspace_name'] ?? ($validated['name'] . "'s Workspace");
            $workspace = Workspace::create([
                'name' => $workspaceName,
                'slug' => Str::slug($workspaceName) . '-' . Str::random(5),
                'owner_id' => $user->id,
            ]);

            // Assign Owner role
            WorkspaceMember::create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'workspace' => $workspace,
                'token' => $token,
            ];
        });

        return response()->json([
            'message' => 'Registration successful',
            'user' => [
                'id' => $result['user']->id,
                'name' => $result['user']->name,
                'email' => $result['user']->email,
            ],
            'workspace' => [
                'id' => $result['workspace']->id,
                'name' => $result['workspace']->name,
                'slug' => $result['workspace']->slug,
            ],
            'token' => $result['token'],
        ], 201);
    }

    /**
     * Authenticate user and issue API bearer token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password credentials.',
            ], 401);
        }

        $deviceName = $validated['device_name'] ?? 'web_app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'workspaces' => $user->workspaces()->get(['workspaces.id', 'workspaces.name', 'workspaces.slug', 'workspace_members.role']),
            'token' => $token,
        ]);
    }

    /**
     * Get authenticated user profile and workspaces.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'workspaces' => $user->workspaces()->get(['workspaces.id', 'workspaces.name', 'workspaces.slug', 'workspace_members.role']),
        ]);
    }

    /**
     * Revoke current authentication token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }
}
