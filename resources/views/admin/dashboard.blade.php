@extends('admin.layouts.app')

@section('title', 'Executive Overview')
@section('header_title', 'Executive Overview')
@section('header_subtitle', 'Global SaaS Key Metrics & System Telemetry')

@section('content')
<div class="space-y-8">

    <!-- KPI Metric Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Card 1: Estimated MRR -->
        <div class="glass-panel p-6 rounded-2xl relative overflow-hidden glass-card-hover group">
            <div class="absolute -top-10 -right-10 w-28 h-28 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition duration-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Monthly Recurring Revenue</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 text-sm">💵</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white tracking-tight">${{ number_format($estimatedMrr) }}</span>
                <span class="text-xs font-semibold text-emerald-400">MRR</span>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span>{{ $activeSubscriptions }} Active Subscriptions</span>
                <span class="text-emerald-400 font-semibold">Stripe Connected</span>
            </div>
        </div>

        <!-- Card 2: Tenant Workspaces -->
        <div class="glass-panel p-6 rounded-2xl relative overflow-hidden glass-card-hover group">
            <div class="absolute -top-10 -right-10 w-28 h-28 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/20 transition duration-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tenant Workspaces</span>
                <span class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400 text-sm">🏢</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white tracking-tight">{{ number_format($workspaceCount) }}</span>
                <span class="text-xs font-semibold text-indigo-400">Tenants</span>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span>Multi-tenant Isolation</span>
                <a href="{{ route('admin.workspaces.index') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">View All &rarr;</a>
            </div>
        </div>

        <!-- Card 3: Platform Users & IAM -->
        <div class="glass-panel p-6 rounded-2xl relative overflow-hidden glass-card-hover group">
            <div class="absolute -top-10 -right-10 w-28 h-28 bg-cyan-500/10 rounded-full blur-2xl group-hover:bg-cyan-500/20 transition duration-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Users</span>
                <span class="p-2 rounded-xl bg-cyan-500/10 text-cyan-400 text-sm">👥</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white tracking-tight">{{ number_format($userCount) }}</span>
                <span class="text-xs font-semibold text-cyan-400">Accounts</span>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span>{{ $superAdminCount }} Super Admins</span>
                <a href="{{ route('admin.users.index') }}" class="text-cyan-400 hover:text-cyan-300 font-semibold transition">Manage IAM &rarr;</a>
            </div>
        </div>

        <!-- Card 4: AI Gateway Burn Velocity -->
        <div class="glass-panel p-6 rounded-2xl relative overflow-hidden glass-card-hover group">
            <div class="absolute -top-10 -right-10 w-28 h-28 bg-violet-500/10 rounded-full blur-2xl group-hover:bg-violet-500/20 transition duration-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">AI Credits Consumed</span>
                <span class="p-2 rounded-xl bg-violet-500/10 text-violet-400 text-sm">⚡</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white tracking-tight">{{ number_format($totalCreditsConsumed) }}</span>
                <span class="text-xs text-slate-400 font-normal">/ {{ number_format($totalCreditsAllocated) }}</span>
            </div>
            <!-- Progress bar -->
            <div class="w-full bg-slate-800/90 rounded-full h-1.5 mt-3 overflow-hidden">
                <div class="bg-gradient-to-r from-violet-500 to-indigo-400 h-1.5 rounded-full" style="width: {{ min(100, $creditUtilizationPercent) }}%"></div>
            </div>
            <div class="mt-3 pt-2 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span>{{ $creditUtilizationPercent }}% Burn Rate</span>
                <a href="{{ route('admin.ai.sandbox') }}" class="text-violet-400 hover:text-violet-300 font-semibold transition">LLM Sandbox &rarr;</a>
            </div>
        </div>

    </div>

    <!-- Main Grid: Live Health Cockpit + Audit Stream -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Column 1: Live Infrastructure Cockpit -->
        <div class="glass-panel p-6 rounded-2xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-2.5 h-2.5 rounded-full {{ $health['status'] === 'healthy' ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400 animate-ping' }}"></div>
                    <h2 class="text-sm font-bold text-white">Live Infrastructure Cockpit</h2>
                </div>
                <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full {{ $health['status'] === 'healthy' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                    {{ strtoupper($health['status']) }}
                </span>
            </div>

            <!-- Health Sub-Checks -->
            <div class="space-y-3">
                @foreach($health['checks'] as $serviceName => $check)
                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg bg-slate-800/80 flex items-center justify-center text-xs">
                            @if($serviceName === 'database') 🗄️
                            @elseif($serviceName === 'redis') ⚡
                            @elseif($serviceName === 'storage') 📁
                            @elseif($serviceName === 'disk_space') 💾
                            @else 🔧
                            @endif
                        </span>
                        <div>
                            <div class="text-xs font-bold text-slate-200 capitalize">{{ str_replace('_', ' ', $serviceName) }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">
                                @if(isset($check['latency_ms']))
                                    Latency: {{ $check['latency_ms'] }}ms
                                @elseif(isset($check['free_percentage']))
                                    {{ $check['free_percentage'] }}% Available
                                @else
                                    Operational
                                @endif
                            </div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $check['status'] === 'ok' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                        {{ strtoupper($check['status']) }}
                    </span>
                </div>
                @endforeach
            </div>

            <!-- Quick Operator Actions -->
            <div class="pt-4 border-t border-slate-800/80 space-y-2">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Operator Fast-Actions</div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.workspaces.index') }}" class="p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 text-xs font-semibold text-slate-200 text-center transition flex items-center justify-center gap-1.5">
                        <span>🏢 Workspaces</span>
                    </a>
                    <a href="{{ route('admin.features.index') }}" class="p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 text-xs font-semibold text-slate-200 text-center transition flex items-center justify-center gap-1.5">
                        <span>🚩 Flags ({{ $activeFlagsCount }})</span>
                    </a>
                    <a href="{{ route('admin.ai.sandbox') }}" class="p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 text-xs font-semibold text-slate-200 text-center transition flex items-center justify-center gap-1.5">
                        <span>🤖 Prompt Tester</span>
                    </a>
                    <a href="{{ route('admin.monitoring.index') }}" class="p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 text-xs font-semibold text-slate-200 text-center transition flex items-center justify-center gap-1.5">
                        <span>🩺 Telemetry</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Column 2 & 3: Immutable Compliance Audit Stream -->
        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📜</span>
                        <h2 class="text-sm font-bold text-white">Immutable Security & Governance Audit Trail</h2>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded border border-slate-700/60">
                        Zero-Tamper Log
                    </span>
                </div>

                <!-- Feed Table -->
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-800/80 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-2.5 px-3">Event</th>
                                <th class="py-2.5 px-3">Operator</th>
                                <th class="py-2.5 px-3">Target / Scope</th>
                                <th class="py-2.5 px-3">IP Address</th>
                                <th class="py-2.5 px-3 text-right">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            @forelse($recentAuditLogs as $log)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3 px-3 font-semibold text-slate-200">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-300">
                                    {{ $log->user->name ?? 'System' }}
                                </td>
                                <td class="py-3 px-3 text-slate-400">
                                    {{ $log->workspace->name ?? 'Global' }}
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px] text-slate-400">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                                <td class="py-3 px-3 text-right text-slate-500 text-[11px]">
                                    {{ $log->created_at->diffForHumans() }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500 italic">
                                    No compliance audit events recorded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span>Showing latest {{ $recentAuditLogs->count() }} cryptographic event entries</span>
                <a href="{{ route('admin.monitoring.index') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">
                    View Full Telemetry &rarr;
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
