@extends('admin.layouts.app')

@section('title', 'Feature Flags Matrix')
@section('header_title', 'Feature Flags Matrix')
@section('header_subtitle', 'Global Toggles, Tenant Overrides & Circuit Breakers')

@section('content')
<div class="space-y-8">

    <!-- Section 1: Global Feature Flags Cards -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-bold text-white flex items-center gap-2">
                    <span>🚩 Global Feature Flags</span>
                </h2>
                <p class="text-xs text-slate-400">Platform-wide defaults affecting all tenants unless overridden at workspace level.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($activeFlags as $key => $flag)
            <div class="glass-panel p-5 rounded-2xl flex flex-col justify-between border {{ $flag['is_enabled'] ? 'border-indigo-500/30' : 'border-slate-800' }} glass-card-hover">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold {{ $flag['is_enabled'] ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-slate-800 text-slate-400' }}">
                            {{ $key }}
                        </span>

                        <span class="flex items-center gap-1.5 text-[11px] font-bold {{ $flag['is_enabled'] ? 'text-emerald-400' : 'text-slate-500' }}">
                            <span class="w-2 h-2 rounded-full {{ $flag['is_enabled'] ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600' }}"></span>
                            {{ $flag['is_enabled'] ? 'ACTIVE' : 'DISABLED' }}
                        </span>
                    </div>

                    <h3 class="text-sm font-bold text-white mt-3 capitalize">
                        {{ str_replace('_', ' ', $key) }}
                    </h3>

                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                        {{ $flag['description'] }}
                    </p>
                </div>

                <div class="mt-5 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                    <span class="text-[10px] text-slate-500 font-medium">
                        {{ $flag['is_overridden'] ? 'Database Override' : 'Config Default' }}
                    </span>

                    <form action="{{ route('admin.features.toggle') }}" method="POST">
                        @csrf
                        <input type="hidden" name="feature_key" value="{{ $key }}">
                        <input type="hidden" name="is_enabled" value="{{ $flag['is_enabled'] ? '0' : '1' }}">
                        <button type="submit"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm {{ $flag['is_enabled'] ? 'bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20' : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-indigo-600/25' }}">
                            {{ $flag['is_enabled'] ? 'Disable Flag' : 'Enable Flag' }}
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Section 2: Tenant-Specific Overrides -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Form: Add Tenant Override -->
        <div class="glass-panel p-6 rounded-2xl h-fit">
            <h3 class="text-sm font-bold text-white flex items-center gap-2 mb-1">
                <span>⚡ Add Tenant Override</span>
            </h3>
            <p class="text-xs text-slate-400 mb-4">Grant or revoke specific capabilities for a single enterprise tenant.</p>

            <form action="{{ route('admin.features.override') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Target Tenant Workspace</label>
                    <select name="workspace_id" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-indigo-500">
                        <option value="" disabled selected>Select a workspace...</option>
                        @foreach($workspaces as $ws)
                            <option value="{{ $ws->id }}">{{ $ws->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Feature Key</label>
                    <select name="feature_key" required class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-indigo-500">
                        @foreach(array_keys($activeFlags) as $featureName)
                            <option value="{{ $featureName }}">{{ $featureName }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Override State</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-900 border border-slate-700 cursor-pointer hover:border-indigo-500">
                            <input type="radio" name="is_enabled" value="1" checked class="text-indigo-600">
                            <span class="text-emerald-400 font-bold">Enabled</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-900 border border-slate-700 cursor-pointer hover:border-rose-500">
                            <input type="radio" name="is_enabled" value="0" class="text-rose-600">
                            <span class="text-rose-400 font-bold">Disabled</span>
                        </label>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition shadow-lg shadow-indigo-600/25">
                        Apply Tenant Override
                    </button>
                </div>
            </form>
        </div>

        <!-- Table: Active Tenant Overrides -->
        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl">
            <h3 class="text-sm font-bold text-white flex items-center gap-2 mb-1">
                <span>🏢 Active Tenant Overrides ({{ $tenantOverrides->count() }})</span>
            </h3>
            <p class="text-xs text-slate-400 mb-4">Workspace-specific exceptions bypassing global feature defaults.</p>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="py-2.5 px-3">Workspace</th>
                            <th class="py-2.5 px-3">Feature Key</th>
                            <th class="py-2.5 px-3">Override Status</th>
                            <th class="py-2.5 px-3">Applied</th>
                            <th class="py-2.5 px-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @forelse($tenantOverrides as $override)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-3 font-semibold text-white">
                                {{ $override->workspace->name ?? 'Unknown Workspace' }}
                            </td>
                            <td class="py-3 px-3 font-mono text-[11px] text-indigo-300">
                                {{ $override->feature_key }}
                            </td>
                            <td class="py-3 px-3">
                                @if($override->is_enabled)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        FORCE ENABLED
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        FORCE DISABLED
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-400 text-[11px]">
                                {{ $override->updated_at ? $override->updated_at->diffForHumans() : 'N/A' }}
                            </td>
                            <td class="py-3 px-3 text-right">
                                <form action="{{ route('admin.features.override.delete', $override->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 font-medium text-xs transition" title="Delete override and revert to default">
                                        Revert &times;
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 italic">
                                No workspace-specific overrides configured. All tenants use global flags.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
