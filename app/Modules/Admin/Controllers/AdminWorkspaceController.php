<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Metering\Models\UsageMeter;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Services\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminWorkspaceController extends Controller
{
    /**
     * Display searchable list of all tenant workspaces.
     */
    public function index(Request $request): View
    {
        $query = Workspace::with(['members.user', 'activeSubscription'])
            ->withCount('members');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $workspaces = $query->latest()->paginate(15)->withQueryString();

        // Attach usage meters for each workspace without global tenant scope
        $workspaceIds = $workspaces->pluck('id')->toArray();
        $meters = UsageMeter::withoutGlobalScopes()
            ->whereIn('workspace_id', $workspaceIds)
            ->where('metric_name', 'ai_credits')
            ->get()
            ->keyBy('workspace_id');

        return view('admin.workspaces.index', compact('workspaces', 'meters'));
    }

    /**
     * Adjust credit / resource quota for a tenant.
     */
    public function updateQuota(Request $request, string $id): RedirectResponse
    {
        $workspace = Workspace::findOrFail($id);

        $validated = $request->validate([
            'metric_name' => 'required|string|max:64',
            'allocated_limit' => 'required|integer|min:0|max:1000000',
        ]);

        $meter = UsageMeter::withoutGlobalScopes()->firstOrNew([
            'workspace_id' => $workspace->id,
            'metric_name' => $validated['metric_name'],
        ]);

        $oldLimit = $meter->allocated_limit ?? 0;
        $meter->allocated_limit = (int) $validated['allocated_limit'];
        $meter->current_usage = $meter->current_usage ?? 0;
        $meter->save();

        // Audit log compliance record
        AuditLog::withoutGlobalScopes()->create([
            'workspace_id' => $workspace->id,
            'user_id' => auth()->id(),
            'event' => 'admin.quota_adjusted',
            'auditable_type' => UsageMeter::class,
            'auditable_id' => (string) $meter->id,
            'old_values' => ['allocated_limit' => $oldLimit],
            'new_values' => ['allocated_limit' => $meter->allocated_limit],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Quota for '{$workspace->name}' updated to {$meter->allocated_limit} credits.");
    }

    /**
     * Start impersonating a tenant workspace.
     */
    public function impersonate(Request $request, string $id): RedirectResponse
    {
        $workspace = Workspace::findOrFail($id);

        $request->session()->put('admin_impersonator_id', auth()->id());
        $request->session()->put('impersonated_workspace_id', $workspace->id);
        $request->session()->put('impersonated_workspace_name', $workspace->name);

        app(TenantContext::class)->setWorkspace($workspace);

        return redirect()->route('dashboard')->with('info', "Now impersonating tenant '{$workspace->name}'.");
    }

    /**
     * Stop impersonating and return to Admin Command Center.
     */
    public function stopImpersonating(Request $request): RedirectResponse
    {
        $workspaceName = $request->session()->get('impersonated_workspace_name', 'Tenant');

        $request->session()->forget(['admin_impersonator_id', 'impersonated_workspace_id', 'impersonated_workspace_name']);
        app(TenantContext::class)->clear();

        return redirect()->route('admin.workspaces.index')->with('success', "Ended impersonation of {$workspaceName}.");
    }
}
