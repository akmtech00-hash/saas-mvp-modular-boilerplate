<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Auth\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Display searchable list of SaaS platform users.
     */
    public function index(Request $request): View
    {
        $query = User::withCount('workspaces');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Promote or demote a user to/from Super Administrator.
     */
    public function toggleSuperAdmin(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Prevent self-lockout
        if ($user->id === auth()->id() && $user->is_super_admin) {
            return back()->with('error', 'You cannot demote your own Super Administrator account.');
        }

        $newStatus = !$user->is_super_admin;
        $user->is_super_admin = $newStatus;
        $user->save();

        // Audit log compliance record
        AuditLog::withoutGlobalScopes()->create([
            'workspace_id' => null,
            'user_id' => auth()->id(),
            'event' => $newStatus ? 'admin.user_promoted' : 'admin.user_demoted',
            'auditable_type' => User::class,
            'auditable_id' => (string) $user->id,
            'old_values' => ['is_super_admin' => !$newStatus],
            'new_values' => ['is_super_admin' => $newStatus],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $action = $newStatus ? 'promoted to' : 'demoted from';
        return back()->with('success', "User '{$user->name}' was {$action} Super Administrator.");
    }
}
