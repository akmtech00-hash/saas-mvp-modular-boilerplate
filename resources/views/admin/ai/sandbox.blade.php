@extends('admin.layouts.app')

@section('title', 'AI Gateway Prompt Sandbox')
@section('header_title', 'AI Gateway Prompt Sandbox')
@section('header_subtitle', 'Multi-LLM Playground, Real-Time Token Breakdown & Credit Auditing')

@section('content')
<div class="space-y-8">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Left Column: Prompt Configuration & Input (5 cols) -->
        <div class="lg:col-span-5 glass-panel p-6 rounded-2xl space-y-5 h-fit">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="text-base">🤖</span>
                    <h2 class="text-sm font-bold text-white">Inference Engine Parameters</h2>
                </div>
                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    Unified Gateway
                </span>
            </div>

            <form action="{{ route('admin.ai.sandbox.run') }}" method="POST" id="aiForm" class="space-y-4 text-xs">
                @csrf

                <!-- Model Selector -->
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Target Foundation Model</label>
                    <select name="model" id="modelSelect" required class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-indigo-500 font-medium">
                        @foreach($models as $key => $label)
                            <option value="{{ $key }}" {{ old('model', 'gemini-1.5-pro') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Optional Workspace Context -->
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">
                        Billing & Tenant Context <span class="text-slate-500 font-normal">(Optional)</span>
                    </label>
                    <select name="workspace_id" class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-indigo-500">
                        <option value="">Direct Admin Sandbox (0 Credits Deducted)</option>
                        @foreach($workspaces as $ws)
                            <option value="{{ $ws->id }}" {{ old('workspace_id') === $ws->id ? 'selected' : '' }}>
                                Deduct from: {{ $ws->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Temperature Slider -->
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="font-semibold text-slate-300">Creativity / Temperature</label>
                        <span id="tempValue" class="font-mono text-indigo-400 font-bold">0.7</span>
                    </div>
                    <input type="range" name="temperature" id="tempRange" min="0" max="1.5" step="0.1" value="0.7"
                           class="w-full accent-indigo-500 bg-slate-800 rounded-lg cursor-pointer"
                           oninput="document.getElementById('tempValue').innerText = this.value">
                </div>

                <!-- Prompt Preset Buttons -->
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Quick Prompt Presets</div>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="setPreset('Explain how atomic credit locking prevents race conditions in multi-tenant SaaS architectures.')"
                                class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] transition">
                            ⚡ Concurrency
                        </button>
                        <button type="button" onclick="setPreset('Draft a high-converting B2B SaaS onboarding welcome email for a Pro plan customer.')"
                                class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] transition">
                            ✉️ Welcome Email
                        </button>
                        <button type="button" onclick="setPreset('Analyze the security implications of HMAC signed webhooks with timestamp replay validation.')"
                                class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] transition">
                            🔒 Webhooks Security
                        </button>
                    </div>
                </div>

                <!-- Prompt Textarea -->
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Prompt Instruction</label>
                    <textarea name="prompt" id="promptInput" rows="6" required
                              placeholder="Enter your LLM prompt here..."
                              class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 leading-relaxed font-mono text-xs">{{ old('prompt', 'Provide an executive summary of best practices for building modular SaaS boilerplates with Laravel 11.') }}</textarea>
                </div>

                <button type="submit" id="runBtn" class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-xs transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2">
                    <span>⚡ Run Inference & Audit</span>
                </button>
            </form>
        </div>

        <!-- Right Column: Generation Output & Telemetry (7 cols) -->
        <div class="lg:col-span-7 glass-panel p-6 rounded-2xl flex flex-col justify-between space-y-6">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📜</span>
                        <h2 class="text-sm font-bold text-white">Inference Response & Audit Breakdown</h2>
                    </div>

                    @if(session('ai_result'))
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Success ({{ session('ai_result')['latency_ms'] }}ms)
                    </span>
                    @else
                    <span class="text-[11px] text-slate-500">Awaiting prompt execution...</span>
                    @endif
                </div>

                @if(session('ai_result'))
                @php $res = session('ai_result'); @endphp
                <!-- Telemetry Cards Row -->
                <div class="grid grid-cols-4 gap-3 my-4">
                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 text-center">
                        <div class="text-[10px] font-bold uppercase text-slate-500">Latency</div>
                        <div class="text-sm font-extrabold text-white font-mono mt-0.5">{{ $res['latency_ms'] }} ms</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 text-center">
                        <div class="text-[10px] font-bold uppercase text-slate-500">Prompt Tokens</div>
                        <div class="text-sm font-extrabold text-cyan-400 font-mono mt-0.5">{{ $res['tokens']['prompt_tokens'] }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 text-center">
                        <div class="text-[10px] font-bold uppercase text-slate-500">Output Tokens</div>
                        <div class="text-sm font-extrabold text-indigo-400 font-mono mt-0.5">{{ $res['tokens']['completion_tokens'] }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 text-center">
                        <div class="text-[10px] font-bold uppercase text-slate-500">Credits Cost</div>
                        <div class="text-sm font-extrabold text-violet-400 font-mono mt-0.5">
                            {{ $res['credits_deducted'] > 0 ? $res['credits_deducted'] . ' deducted' : ($res['credits_estimated'] ?? 1) . ' estimated' }}
                        </div>
                    </div>
                </div>

                <!-- LLM Response Content Box -->
                <div class="p-4 rounded-xl bg-slate-900/90 border border-slate-800/80 text-xs text-slate-200 leading-relaxed font-mono whitespace-pre-wrap max-h-96 overflow-y-auto">
{{ $res['content'] }}
                </div>
                @else
                <div class="py-24 text-center text-slate-500 text-xs italic">
                    <div class="text-3xl mb-2">⚡</div>
                    Select an LLM model, choose an optional tenant context, and submit a prompt to test inference.
                </div>
                @endif
            </div>

            <!-- Architecture Info Footer -->
            <div class="pt-4 border-t border-slate-800/80 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Rate: <strong>1 credit per 500 tokens</strong></span>
                <span class="font-mono text-slate-500">Drivers: Gemini &bull; OpenAI &bull; Mock</span>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    function setPreset(text) {
        document.getElementById('promptInput').value = text;
    }
</script>
@endpush
@endsection
