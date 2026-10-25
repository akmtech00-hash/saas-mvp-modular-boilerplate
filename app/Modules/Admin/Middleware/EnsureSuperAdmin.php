<?php

declare(strict_types=1);

namespace App\Modules\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Ensure the authenticated user has Super Admin authority.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'unauthenticated', 'message' => 'Authentication required.'], 401);
            }
            return redirect()->route('dashboard')->with('error', 'Please authenticate to access the Admin Console.');
        }

        if (!$user->is_super_admin) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'forbidden', 'message' => 'Super Administrator privilege required.'], 403);
            }
            abort(403, 'Forbidden. Super Administrator privileges required to access the Admin Console.');
        }

        return $next($request);
    }
}
