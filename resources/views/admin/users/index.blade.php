@extends('admin.layouts.app')

@section('title', 'User Directory & IAM')
@section('header_title', 'User Directory & IAM')
@section('header_subtitle', 'Identity & Access Management, Super Admin Privileges')

@section('content')
<div class="space-y-6">

    <!-- Search & Filter Bar -->
    <div class="glass-panel p-4 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.users.index') }}" method="GET" class="w-full sm:w-96 flex items-center gap-2">
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500 text-xs">
                    🔍
                </span>
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Search user by name or email..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-700/80 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition shadow-md shadow-indigo-600/20">
                Search
            </button>
            @if(request('q'))
            <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs transition">
                Clear
            </a>
            @endif
        </form>

        <div class="flex items-center gap-3 text-xs text-slate-400">
            <span>Total Accounts: <strong class="text-white">{{ $users->total() }}</strong></span>
        </div>
    </div>

    <!-- Users Table -->
    <div class="glass-panel rounded-2xl overflow-hidden border border-slate-800/80">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900/90 border-b border-slate-800 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">User ID</th>
                        <th class="py-3 px-4">Workspaces</th>
                        <th class="py-3 px-4">Authority Level</th>
                        <th class="py-3 px-4">Registered</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-800/30 transition">
                        <!-- User Name & Email -->
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl {{ $user->is_super_admin ? 'bg-gradient-to-tr from-amber-500 to-indigo-600' : 'bg-slate-800' }} border border-white/10 flex items-center justify-center font-bold text-white text-xs">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-white text-sm">{{ $user->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- ID -->
                        <td class="py-4 px-4 font-mono text-[11px] text-slate-400">
                            #{{ $user->id }}
                        </td>

                        <!-- Workspaces Count -->
                        <td class="py-4 px-4 text-slate-300">
                            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700/60 font-medium text-[11px]">
                                {{ $user->workspaces_count }} {{ Str::plural('workspace', $user->workspaces_count) }}
                            </span>
                        </td>

                        <!-- Authority Level -->
                        <td class="py-4 px-4">
                            @if($user->is_super_admin)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center gap-1.5 w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                    SUPER ADMIN
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700/60 w-fit">
                                    STANDARD USER
                                </span>
                            @endif
                        </td>

                        <!-- Registered -->
                        <td class="py-4 px-4 text-slate-400 text-[11px]">
                            {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-4 text-right">
                            <form action="{{ route('admin.users.toggle-admin', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to change Super Admin privileges for {{ addslashes($user->name) }}?');">
                                @csrf
                                @if($user->is_super_admin)
                                    <button type="submit"
                                            @if($user->id === auth()->id()) disabled title="Cannot demote yourself" class="opacity-40 cursor-not-allowed px-3 py-1.5 rounded-lg bg-slate-800 text-slate-400 text-[11px] font-semibold" @else class="px-3 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 text-[11px] font-semibold transition" @endif>
                                        Demote from Admin
                                    </button>
                                @else
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600/80 hover:bg-indigo-500 text-white text-[11px] font-semibold transition shadow-sm">
                                        ⚡ Grant Super Admin
                                    </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500 italic">
                            No users found matching your search.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-800/80 bg-slate-900/40">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
