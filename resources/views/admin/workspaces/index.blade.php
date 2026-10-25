@extends('admin.layouts.app')

@section('title', 'Tenants & Workspaces')
@section('header_title', 'Tenant Workspaces')
@section('header_subtitle', 'Multi-tenant Directory, Quota Adjustments & Impersonation')

@section('content')
<div class="space-y-6">

    <!-- Search & Filter Bar -->
    <div class="glass-panel p-4 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.workspaces.index') }}" method="GET" class="w-full sm:w-96 flex items-center gap-2">
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500 text-xs">
                    🔍
                </span>
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Search tenant by name, slug, or UUID..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-700/80 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition shadow-md shadow-indigo-600/20">
                Search
            </button>
            @if(request('q'))
            <a href="{{ route('admin.workspaces.index') }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs transition">
                Clear
            </a>
            @endif
        </form>

        <div class="flex items-center gap-3 text-xs text-slate-400">
            <span>Total Workspaces: <strong class="text-white">{{ $workspaces->total() }}</strong></span>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="glass-panel rounded-2xl overflow-hidden border border-slate-800/80">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900/90 border-b border-slate-800 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-3 px-4">Workspace</th>
                        <th class="py-3 px-4">Tenant UUID</th>
                        <th class="py-3 px-4">Members</th>
                        <th class="py-3 px-4">Subscription</th>
                        <th class="py-3 px-4">AI Credits Quota</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($workspaces as $workspace)
                    @php
                        $meter = $meters[$workspace->id] ?? null;
                        $limit = $meter ? $meter->allocated_limit : 100;
                        $used = $meter ? $meter->current_usage : 0;
                        $percent = $limit > 0 ? min(100, round(($used / $limit) * 100)) : 0;
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <!-- Name & Slug -->
                        <td class="py-4 px-4">
                            <div class="font-bold text-white text-sm flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs">
                                    🏢
                                </div>
                                <div>
                                    <div>{{ $workspace->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $workspace->slug }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- UUID -->
                        <td class="py-4 px-4 font-mono text-[11px] text-indigo-300">
                            {{ $workspace->id }}
                        </td>

                        <!-- Members Count -->
                        <td class="py-4 px-4 text-slate-300">
                            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700/60 font-medium text-[11px]">
                                {{ $workspace->members_count }} {{ Str::plural('member', $workspace->members_count) }}
                            </span>
                        </td>

                        <!-- Subscription -->
                        <td class="py-4 px-4">
                            @if($workspace->activeSubscription)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    PRO TIER &bull; ACTIVE
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700/60">
                                    STARTER (FREE)
                                </span>
                            @endif
                        </td>

                        <!-- AI Credits Quota Meter -->
                        <td class="py-4 px-4">
                            <div class="w-48">
                                <div class="flex justify-between text-[11px] mb-1">
                                    <span class="text-slate-300 font-semibold">{{ $used }} / {{ $limit }}</span>
                                    <span class="text-slate-400">{{ $percent }}%</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-gradient-to-r from-indigo-500 to-cyan-400 h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Quota Adjustment Trigger -->
                                <button onclick="openQuotaModal('{{ $workspace->id }}', '{{ addslashes($workspace->name) }}', {{ $limit }})"
                                        class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 text-[11px] font-semibold transition"
                                        title="Adjust resource quota">
                                    ⚙️ Quota
                                </button>

                                <!-- Impersonate Tenant Button -->
                                <form action="{{ route('admin.workspaces.impersonate', $workspace->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="px-2.5 py-1.5 rounded-lg bg-indigo-600/80 hover:bg-indigo-500 text-white text-[11px] font-semibold transition shadow-sm flex items-center gap-1"
                                            title="Impersonate and browse as this workspace">
                                        <span>⚡ Impersonate</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500 italic">
                            No tenant workspaces found matching your query.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workspaces->hasPages())
        <div class="p-4 border-t border-slate-800/80 bg-slate-900/40">
            {{ $workspaces->links() }}
        </div>
        @endif
    </div>

</div>

<!-- Quota Adjustment Modal -->
<div id="quotaModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="glass-panel w-full max-w-md p-6 rounded-2xl border border-indigo-500/20 shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>⚙️ Adjust Tenant Quota</span>
            </h3>
            <button onclick="closeQuotaModal()" class="text-slate-400 hover:text-white text-lg leading-none">&times;</button>
        </div>

        <form id="quotaForm" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Target Workspace</label>
                <input type="text" id="modalWorkspaceName" readonly class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-400 font-medium">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Resource Metric</label>
                <select name="metric_name" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white focus:outline-none focus:border-indigo-500">
                    <option value="ai_credits">AI Inference Credits (ai_credits)</option>
                    <option value="api_requests">Monthly API Requests (api_requests)</option>
                    <option value="seats">Team Member Seats (seats)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">New Allocated Limit</label>
                <input type="number"
                       name="allocated_limit"
                       id="modalAllocatedLimit"
                       min="0"
                       max="1000000"
                       required
                       class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white focus:outline-none focus:border-indigo-500">
                <span class="text-[10px] text-slate-500 mt-1 block">Allocating credits will trigger an immutable compliance audit record.</span>
            </div>

            <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeQuotaModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold hover:bg-slate-700 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-lg shadow-indigo-600/25">
                    Save Quota
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openQuotaModal(workspaceId, workspaceName, currentLimit) {
        const modal = document.getElementById('quotaModal');
        const form = document.getElementById('quotaForm');
        const nameInput = document.getElementById('modalWorkspaceName');
        const limitInput = document.getElementById('modalAllocatedLimit');

        form.action = `/admin/workspaces/${workspaceId}/quota`;
        nameInput.value = workspaceName;
        limitInput.value = currentLimit;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeQuotaModal() {
        const modal = document.getElementById('quotaModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
@endsection
