<!DOCTYPE html>
<html lang="en" class="h-full bg-[#070b14] text-slate-100 antialiased selection:bg-indigo-500 selection:text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Command Center') &bull; SaaS Modular Monolith v2.0</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#111827',
                            900: '#0f172a',
                            950: '#070b14',
                        },
                        brand: {
                            50: '#eef2ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; }
        code, pre, .font-mono { font-family: 'JetBrains Mono', monospace; }

        .glass-panel {
            background: rgba(15, 23, 42, 0.72);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.07);
        }

        .glass-card-hover {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card-hover:hover {
            transform: translateY(-2px);
            border-color: rgba(99, 102, 241, 0.35);
            box-shadow: 0 12px 30px -10px rgba(99, 102, 241, 0.15);
        }

        .glow-indigo {
            box-shadow: 0 0 25px -5px rgba(99, 102, 241, 0.3);
        }

        .glow-emerald {
            box-shadow: 0 0 20px -5px rgba(16, 185, 129, 0.3);
        }

        /* Subtle scrollbars */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #070b14; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
    </style>
</head>
<body class="min-h-full flex bg-[#070b14] text-slate-100 selection:bg-indigo-500 selection:text-white">

    <!-- Impersonation Active Banner -->
    @if(session()->has('admin_impersonator_id'))
    <div class="fixed top-0 left-0 right-0 z-50 bg-amber-500/20 backdrop-blur-md border-b border-amber-500/30 px-4 py-2.5 text-xs text-amber-200 flex items-center justify-between shadow-lg shadow-amber-950/40">
        <div class="flex items-center gap-2 max-w-7xl mx-auto w-full justify-between">
            <div class="flex items-center gap-2 font-medium">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                <span>⚡ <strong>ACTIVE TENANT IMPERSONATION:</strong> You are operating as workspace <em>{{ session('impersonated_workspace_name', 'Tenant') }}</em></span>
            </div>
            <form action="{{ route('admin.impersonate.stop') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-3 py-1 rounded-md bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 transition text-xs shadow-md">
                    Return to Admin Console &rarr;
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Sidebar Navigation -->
    <aside class="w-64 flex-shrink-0 bg-slate-900/80 border-r border-slate-800/80 backdrop-blur-xl flex flex-col justify-between fixed inset-y-0 z-40 {{ session()->has('admin_impersonator_id') ? 'pt-10' : '' }}">
        <div>
            <!-- Header Brand -->
            <div class="h-16 px-6 flex items-center gap-3 border-b border-slate-800/80">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-violet-600 via-indigo-600 to-cyan-400 flex items-center justify-center font-black text-white text-base shadow-lg shadow-indigo-600/30">
                    ⚡
                </div>
                <div>
                    <span class="text-sm font-extrabold tracking-tight text-white block">SaaS Command</span>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-indigo-400 flex items-center gap-1">
                        Release 2.0.0 &bull; Super Admin
                    </span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <div class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                    Platform Management
                </div>

                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60' }}">
                    <span class="text-base">📊</span>
                    <span>Executive Overview</span>
                </a>

                <a href="{{ route('admin.workspaces.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.workspaces.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60' }}">
                    <span class="text-base">🏢</span>
                    <span>Tenants & Workspaces</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60' }}">
                    <span class="text-base">👥</span>
                    <span>User Directory & IAM</span>
                </a>

                <a href="{{ route('admin.features.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.features.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60' }}">
                    <span class="text-base">🚩</span>
                    <span>Feature Flags Matrix</span>
                </a>

                <div class="pt-4 px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                    Observability & AI
                </div>

                <a href="{{ route('admin.monitoring.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.monitoring.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60' }}">
                    <span class="text-base">🩺</span>
                    <span>Health Cockpit</span>
                </a>

                <a href="{{ route('admin.ai.sandbox') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.ai.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60' }}">
                    <span class="text-base">🤖</span>
                    <span>AI LLM Sandbox</span>
                </a>

                <div class="pt-4 px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                    Shortcuts
                </div>

                <a href="/docs" target="_blank"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-400 hover:text-slate-100 hover:bg-slate-800/60 transition">
                    <span class="text-base">📚</span>
                    <span class="flex items-center justify-between w-full">
                        <span>API Docs (Scalar)</span>
                        <span class="text-[10px] text-slate-500 font-mono">↗</span>
                    </span>
                </a>

                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-slate-400 hover:text-slate-100 hover:bg-slate-800/60 transition">
                    <span class="text-base">🚀</span>
                    <span class="flex items-center justify-between w-full">
                        <span>Tenant Portal</span>
                        <span class="text-[10px] text-slate-500 font-mono">&rarr;</span>
                    </span>
                </a>
            </nav>
        </div>

        <!-- Footer / Operator Card -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs">
                    🛡️
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-white truncate">
                        {{ auth()->user()->name ?? 'Super Administrator' }}
                    </div>
                    <div class="text-[10px] text-emerald-400 flex items-center gap-1 font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Admin Authority
                    </div>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-slate-800/50 flex justify-between items-center text-[10px] text-slate-500">
                <span>By <a href="https://tech-akm.com" target="_blank" class="text-slate-400 hover:text-indigo-400 font-medium underline">Tech AKM</a></span>
                <span class="font-mono">v2.0.0</span>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="pl-64 flex-1 flex flex-col min-h-screen {{ session()->has('admin_impersonator_id') ? 'pt-10' : '' }}">

        <!-- Top Header Bar -->
        <header class="h-16 border-b border-slate-800/80 bg-slate-900/50 backdrop-blur-md sticky top-0 z-30 px-8 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-sm font-bold text-slate-200">
                    @yield('header_title', 'Admin Console')
                </h1>
                <span class="text-slate-600">/</span>
                <span class="text-xs text-slate-400">
                    @yield('header_subtitle', 'Modular Governance & Operations')
                </span>
            </div>

            <!-- Health indicator & quick actions -->
            <div class="flex items-center gap-3">
                <!-- System Health Pill -->
                <a href="{{ route('admin.monitoring.index') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold hover:bg-emerald-500/15 transition glow-emerald">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>System Operational</span>
                </a>

                <div class="h-4 w-px bg-slate-800"></div>

                <a href="https://github.com/akmtech00-hash/saas-mvp-modular-boilerplate" target="_blank" class="text-xs font-medium text-slate-400 hover:text-white px-2.5 py-1.5 rounded-lg hover:bg-slate-800 transition flex items-center gap-1.5">
                    <span>GitHub</span>
                    <span class="text-[10px] text-slate-500">↗</span>
                </a>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 p-8 space-y-6">

            <!-- Flash Alerts -->
            @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between shadow-lg shadow-emerald-950/30">
                <div class="flex items-center gap-2.5">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200 font-bold text-sm">&times;</button>
            </div>
            @endif

            @if(session('info'))
            <div class="p-4 rounded-xl bg-cyan-950/40 border border-cyan-500/30 text-cyan-300 text-xs flex items-center justify-between shadow-lg shadow-cyan-950/30">
                <div class="flex items-center gap-2.5">
                    <span class="text-cyan-400 font-bold">ℹ</span>
                    <span>{{ session('info') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-cyan-400 hover:text-cyan-200 font-bold text-sm">&times;</button>
            </div>
            @endif

            @if(session('error') || $errors->any())
            <div class="p-4 rounded-xl bg-rose-950/40 border border-rose-500/30 text-rose-300 text-xs flex items-center justify-between shadow-lg shadow-rose-950/30">
                <div class="flex items-center gap-2.5">
                    <span class="text-rose-400 font-bold">⚠</span>
                    <span>{{ session('error') ?? $errors->first() }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-200 font-bold text-sm">&times;</button>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
