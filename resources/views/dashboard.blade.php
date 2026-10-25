<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS MVP Modular Monolith - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, pre { font-family: 'JetBrains Mono', monospace; }
        .glass-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between selection:bg-indigo-500 selection:text-white">
    <!-- Impersonation Active Banner -->
    @if(session()->has('admin_impersonator_id'))
    <div class="bg-amber-500/20 backdrop-blur-md border-b border-amber-500/30 px-4 py-2.5 text-xs text-amber-200 shadow-lg shadow-amber-950/40 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2 font-medium">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                <span>⚡ <strong>ACTIVE TENANT IMPERSONATION:</strong> You are browsing as workspace <em>{{ session('impersonated_workspace_name', 'Tenant') }}</em></span>
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

    <!-- Navigation Bar -->
    <header class="border-b border-slate-800 bg-slate-900/60 backdrop-blur-md sticky {{ session()->has('admin_impersonator_id') ? 'top-9' : 'top-0' }} z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-500 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/20">
                    🚀
                </div>
                <div>
                    <h1 class="text-base font-bold text-white flex items-center gap-2">
                        SaaS Modular Monolith
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">Laravel 11 + PHP 8.4</span>
                    </h1>
                </div>
            </div>

            <!-- Workspace Switcher & Attribution -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 bg-slate-900 border border-slate-700/80 rounded-lg px-3 py-1.5 text-xs text-slate-300">
                    <span class="text-slate-500">Workspace:</span>
                    <span class="font-bold text-white flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        {{ session('impersonated_workspace_name', 'Acme AI Corp') }}
                    </span>
                </div>
                @if(auth()->check() && (auth()->user()->is_super_admin ?? false) || session()->has('admin_impersonator_id'))
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/30 flex items-center gap-1.5 transition">
                    <span>⚡ Admin Console</span>
                </a>
                @endif
                <a href="/docs" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1.5 transition">
                    <span>📚 API Docs</span>
                </a>
                <a href="https://tech-akm.com" target="_blank" rel="noopener" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 flex items-center gap-1.5 transition">
                    <span>Built by</span>
                    <strong class="underline decoration-indigo-300">Tech AKM</strong>
                    <span>↗</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1 space-y-8">
        
        <!-- Metrics Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Metric 1: Multi-Tenant Workspace -->
            <div class="glass-card p-6 rounded-2xl relative overflow-hidden">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Active Tenant</div>
                <div class="text-xl font-bold text-white mt-1">Acme AI Corp</div>
                <p class="text-xs text-slate-400 mt-2">UUID: <code class="text-indigo-300">8f2c3d10-e5a1-43</code></p>
                <div class="mt-4 pt-3 border-t border-slate-800/80 flex justify-between items-center text-xs">
                    <span class="text-slate-400">Your Role: <strong class="text-slate-200">Owner</strong></span>
                    <span class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-400 font-medium">Tenant Isolated</span>
                </div>
            </div>

            <!-- Metric 2: Stripe Billing Plan -->
            <div class="glass-card p-6 rounded-2xl relative overflow-hidden">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Stripe Subscription</div>
                <div class="text-xl font-bold text-indigo-400 mt-1 flex items-center gap-2">
                    Pro Tier ($49/mo)
                </div>
                <p class="text-xs text-slate-400 mt-2">Status: <span class="text-emerald-400 font-semibold">Active &bull; Renews Nov 1</span></p>
                <div class="mt-4 pt-3 border-t border-slate-800/80 flex justify-between items-center text-xs">
                    <span class="text-slate-400">Seats: <strong class="text-slate-200">5 / 10 Used</strong></span>
                    <a href="/api/v1/billing/plans" class="text-indigo-400 hover:underline font-semibold">Plans API &rarr;</a>
                </div>
            </div>

            <!-- Metric 3: AI Credit Meter -->
            <div class="glass-card p-6 rounded-2xl relative overflow-hidden">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">AI Credit Meter</div>
                <div class="text-xl font-bold text-white mt-1 flex items-baseline gap-2">
                    <span id="credits-left">84</span>
                    <span class="text-xs text-slate-400 font-normal">/ 100 remaining</span>
                </div>
                
                <!-- Progress bar -->
                <div class="w-full bg-slate-800 rounded-full h-2 mt-3 overflow-hidden">
                    <div id="credit-progress" class="bg-gradient-to-r from-indigo-500 to-cyan-400 h-2 rounded-full transition-all duration-300" style="width: 84%"></div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-800/80 flex justify-between items-center text-xs">
                    <span class="text-slate-400">Cycle: <strong class="text-slate-200">22 days left</strong></span>
                    <button id="btn-consume-credit" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-indigo-300 font-medium border border-slate-700 transition">
                        -2 Credits
                    </button>
                </div>
            </div>

            <!-- Metric 4: Platform Observability -->
            <div class="glass-card p-6 rounded-2xl relative overflow-hidden border-emerald-500/20">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Observability Suite</div>
                <div class="text-xl font-bold text-emerald-400 mt-1 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    100% Operational
                </div>
                <p class="text-xs text-slate-400 mt-2">DB, Redis & Storage passing</p>
                <div class="mt-4 pt-3 border-t border-slate-800/80 flex justify-between items-center text-xs">
                    <a href="/health" target="_blank" class="text-emerald-400 hover:underline font-semibold">/health ↗</a>
                    <a href="/metrics" target="_blank" class="text-indigo-400 hover:underline font-semibold">/metrics ↗</a>
                </div>
            </div>
        </div>

        <!-- Architecture Breakdown Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="glass-card p-6 rounded-2xl">
                <h3 class="text-base font-bold text-white mb-2 flex items-center gap-2">
                    <span>🧩</span> Modular Monolith Architecture
                </h3>
                <p class="text-xs text-slate-400 mb-4">Each business capability is encapsulated into its own bounded module under <code class="text-indigo-300">app/Modules/</code>.</p>
                
                <div class="space-y-3">
                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-200">app/Modules/Auth</div>
                            <div class="text-xs text-slate-400">Sanctum tokens, User authentication & onboarding</div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[11px] bg-slate-800 text-slate-300 font-mono">Sanctum</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-200">app/Modules/Workspace</div>
                            <div class="text-xs text-slate-400">Tenant isolation, invitations & TenantScope query guards</div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[11px] bg-slate-800 text-slate-300 font-mono">TenantScope</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-200">app/Modules/Billing</div>
                            <div class="text-xs text-slate-400">Stripe webhooks signature verification & subscription tier gates</div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[11px] bg-slate-800 text-slate-300 font-mono">Stripe Webhook</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-200">app/Modules/Metering</div>
                            <div class="text-xs text-slate-400">Atomic AI credit deductions & transactional audit trail</div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[11px] bg-slate-800 text-slate-300 font-mono">Atomic Lock</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-200">app/Modules/Monitoring</div>
                            <div class="text-xs text-slate-400">Health checks, Prometheus /metrics & X-Correlation-Id tracing</div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[11px] bg-emerald-950 text-emerald-400 border border-emerald-800 font-mono">Prometheus</span>
                    </div>
                </div>
            </div>

            <!-- Inbound Agency CTA Card -->
            <div class="glass-card p-6 rounded-2xl flex flex-col justify-between border-indigo-500/20 bg-gradient-to-br from-slate-900 via-indigo-950/20 to-slate-900">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-semibold mb-3">
                        ⚡ Tech AKM Advisory & Build Service
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Need to launch your SaaS MVP in 4 weeks?</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Don't spend 6 months fighting authentication, multi-tenancy, Stripe webhooks, and rate limiters. Our engineering agency pairs senior software engineers with battle-tested architectures to build your custom SaaS platform fast.
                    </p>
                </div>

                <div class="pt-6 border-t border-slate-800 mt-6 flex flex-col sm:flex-row gap-3">
                    <a href="https://tech-akm.com" target="_blank" rel="noopener" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-lg transition text-center shadow-lg shadow-indigo-600/30">
                        Book SaaS MVP Discovery Call &rarr;
                    </a>
                    <a href="https://tech-akm.com/services" target="_blank" rel="noopener" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs rounded-lg transition text-center border border-slate-700">
                        Explore Full Services
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 bg-slate-900/40 py-6 text-center text-xs text-slate-400">
        <p>Open-source architecture boilerplate by <a href="https://tech-akm.com" target="_blank" rel="noopener" class="text-indigo-400 hover:underline font-semibold">Tech AKM LLC</a> &bull; Production SaaS, Vector AI & Distributed Systems.</p>
    </footer>

    <!-- Interactive Client Script -->
    <script>
        let currentCredits = 84;
        const totalCredits = 100;

        document.getElementById('btn-consume-credit').addEventListener('click', () => {
            if (currentCredits < 2) {
                alert('Insufficient AI credits! Upgrade your subscription to continue.');
                return;
            }
            currentCredits -= 2;
            document.getElementById('credits-left').innerText = currentCredits;
            const pct = (currentCredits / totalCredits) * 100;
            document.getElementById('credit-progress').style.width = `${pct}%`;
        });
    </script>
</body>
</html>
