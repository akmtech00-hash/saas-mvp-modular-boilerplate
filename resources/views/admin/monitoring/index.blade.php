@extends('admin.layouts.app')

@section('title', 'Health & Monitoring Cockpit')
@section('header_title', 'Health & Monitoring Cockpit')
@section('header_subtitle', 'Live Infrastructure Diagnostics, Readiness Probes & Telemetry')

@section('content')
<div class="space-y-8">

    <!-- Top Status Banner -->
    <div class="glass-panel p-6 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 border {{ $health['status'] === 'healthy' ? 'border-emerald-500/30' : 'border-amber-500/30' }}">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl {{ $health['status'] === 'healthy' ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : 'bg-amber-500/10 border border-amber-500/20 text-amber-400' }} flex items-center justify-center text-2xl font-bold">
                {{ $health['status'] === 'healthy' ? '💚' : '⚠️' }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-extrabold text-white">System Status: {{ strtoupper($health['status']) }}</h2>
                    <span class="w-2.5 h-2.5 rounded-full {{ $health['status'] === 'healthy' ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400 animate-ping' }}"></span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">
                    Readiness probes verified across all platform subsystems at <span class="font-mono text-slate-300">{{ $systemInfo['server_time'] }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="/health" target="_blank" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition flex items-center gap-1.5">
                <span>/health JSON</span>
                <span class="text-[10px] text-slate-500 font-mono">↗</span>
            </a>
            <a href="/metrics" target="_blank" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition shadow-md shadow-indigo-600/20 flex items-center gap-1.5">
                <span>/metrics (Prometheus)</span>
                <span class="text-[10px] text-indigo-200 font-mono">↗</span>
            </a>
        </div>
    </div>

    <!-- 4 Diagnostic Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Diagnostic 1: Database -->
        @php $db = $health['checks']['database'] ?? ['status' => 'unknown']; @endphp
        <div class="glass-panel p-5 rounded-2xl space-y-3 glass-card-hover border {{ $db['status'] === 'ok' ? 'border-emerald-500/20' : 'border-rose-500/30' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                    <span>🗄️</span> Database Engine
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $db['status'] === 'ok' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                    {{ strtoupper($db['status']) }}
                </span>
            </div>
            <div class="text-2xl font-black text-white font-mono">
                {{ $db['latency_ms'] ?? 0 }}<span class="text-xs text-slate-400 font-sans font-normal"> ms latency</span>
            </div>
            <div class="pt-2 border-t border-slate-800/80 text-[11px] text-slate-400 flex justify-between">
                <span>Driver:</span>
                <span class="font-mono text-slate-200 uppercase">{{ $systemInfo['database_driver'] }}</span>
            </div>
        </div>

        <!-- Diagnostic 2: Redis Cache -->
        @php $redis = $health['checks']['redis'] ?? ['status' => 'unknown']; @endphp
        <div class="glass-panel p-5 rounded-2xl space-y-3 glass-card-hover border {{ $redis['status'] === 'ok' ? 'border-emerald-500/20' : 'border-rose-500/30' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                    <span>⚡</span> Redis & Cache
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $redis['status'] === 'ok' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                    {{ strtoupper($redis['status']) }}
                </span>
            </div>
            <div class="text-2xl font-black text-white font-mono">
                {{ $redis['latency_ms'] ?? 0 }}<span class="text-xs text-slate-400 font-sans font-normal"> ms ping</span>
            </div>
            <div class="pt-2 border-t border-slate-800/80 text-[11px] text-slate-400 flex justify-between">
                <span>Driver:</span>
                <span class="font-mono text-slate-200 uppercase">{{ $systemInfo['cache_driver'] }}</span>
            </div>
        </div>

        <!-- Diagnostic 3: Storage -->
        @php $storage = $health['checks']['storage'] ?? ['status' => 'unknown']; @endphp
        <div class="glass-panel p-5 rounded-2xl space-y-3 glass-card-hover border {{ $storage['status'] === 'ok' ? 'border-emerald-500/20' : 'border-rose-500/30' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                    <span>📁</span> File Storage
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $storage['status'] === 'ok' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                    {{ strtoupper($storage['status']) }}
                </span>
            </div>
            <div class="text-2xl font-black text-white font-mono">
                Active<span class="text-xs text-slate-400 font-sans font-normal"> R/W verified</span>
            </div>
            <div class="pt-2 border-t border-slate-800/80 text-[11px] text-slate-400 flex justify-between">
                <span>Disk:</span>
                <span class="font-mono text-slate-200">Local / S3</span>
            </div>
        </div>

        <!-- Diagnostic 4: Disk Space -->
        @php $disk = $health['checks']['disk_space'] ?? ['status' => 'unknown']; @endphp
        <div class="glass-panel p-5 rounded-2xl space-y-3 glass-card-hover border {{ $disk['status'] === 'ok' ? 'border-emerald-500/20' : 'border-rose-500/30' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5">
                    <span>💾</span> Disk Capacity
                </span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $disk['status'] === 'ok' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                    {{ strtoupper($disk['status']) }}
                </span>
            </div>
            <div class="text-2xl font-black text-white font-mono">
                {{ $disk['free_percentage'] ?? 0 }}%<span class="text-xs text-slate-400 font-sans font-normal"> available</span>
            </div>
            <div class="pt-2 border-t border-slate-800/80 text-[11px] text-slate-400 flex justify-between">
                <span>Free Space:</span>
                <span class="font-mono text-slate-200">{{ $disk['free_gb'] ?? 0 }} GB</span>
            </div>
        </div>

    </div>

    <!-- Runtime & System Telemetry Spec Table -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Spec 1: Runtime Specifications -->
        <div class="glass-panel p-6 rounded-2xl space-y-4">
            <h3 class="text-sm font-bold text-white flex items-center gap-2 pb-3 border-b border-slate-800">
                <span>⚙️ Runtime & Environment Specifications</span>
            </h3>

            <div class="divide-y divide-slate-800/60 text-xs">
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">PHP Version</span>
                    <span class="font-mono font-bold text-emerald-400">{{ $systemInfo['php_version'] }}</span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">Laravel Framework</span>
                    <span class="font-mono font-bold text-indigo-400">{{ $systemInfo['laravel_version'] }}</span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">Environment (`APP_ENV`)</span>
                    <span class="font-mono px-2 py-0.5 rounded bg-slate-800 text-slate-200">{{ $systemInfo['app_env'] }}</span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">Debug Mode</span>
                    <span class="font-mono text-xs {{ str_contains($systemInfo['app_debug'], 'Production Safe') ? 'text-emerald-400' : 'text-amber-400' }}">
                        {{ $systemInfo['app_debug'] }}
                    </span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">Memory Usage (Current / Peak)</span>
                    <span class="font-mono text-slate-200">{{ $systemInfo['memory_current'] }} / {{ $systemInfo['memory_peak'] }}</span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">Queue Worker Driver</span>
                    <span class="font-mono uppercase text-slate-300">{{ $systemInfo['queue_driver'] }}</span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-400">HTTP Session Driver</span>
                    <span class="font-mono uppercase text-slate-300">{{ $systemInfo['session_driver'] }}</span>
                </div>
            </div>
        </div>

        <!-- Spec 2: Observability & Endpoints Architecture -->
        <div class="glass-panel p-6 rounded-2xl space-y-4">
            <h3 class="text-sm font-bold text-white flex items-center gap-2 pb-3 border-b border-slate-800">
                <span>📡 Prometheus & Tracing Architecture</span>
            </h3>

            <p class="text-xs text-slate-400 leading-relaxed">
                The boilerplate implements high-performance telemetry endpoints designed for Kubernetes health probes, Docker healthchecks, and Prometheus scrapers.
            </p>

            <div class="space-y-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="font-mono font-bold text-indigo-400">GET /health</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Comprehensive subsystem readiness (DB, Redis, Storage)</div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[10px] font-bold">200 OK</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="font-mono font-bold text-indigo-400">GET /health/live & /health/ready</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Kubernetes liveness and readiness probe targets</div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[10px] font-bold">K8s Ready</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="font-mono font-bold text-indigo-400">GET /metrics</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Prometheus format metrics (HTTP requests, durations, memory)</div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-400 text-[10px] font-bold">OpenMetrics</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="font-mono font-bold text-indigo-400">X-Correlation-Id Header</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">End-to-end distributed trace propagation across microservices</div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-violet-500/10 text-violet-400 text-[10px] font-bold">Tracing</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
