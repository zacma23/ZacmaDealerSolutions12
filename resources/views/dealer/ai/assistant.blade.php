@extends('layouts.admin')

@section('title', 'AI CRM Assistant')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-gradient-to-r from-indigo-900 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-md">
        <div class="flex items-center space-x-3 mb-2">
            <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center text-white text-lg">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold">Zacma AI CRM Assistant</h2>
                <p class="text-xs text-indigo-200">Autonomous intelligence strictly scoped to your organization's CRM data</p>
            </div>
        </div>

        <!-- Quick prompt chips -->
        <div class="mt-6">
            <span class="text-[11px] uppercase tracking-wider text-indigo-300 font-bold block mb-2">Example Business Queries:</span>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dealer.ai.assistant', ['query' => 'Show me all hot leads with high purchase intent']) }}" class="bg-white/10 hover:bg-white/20 text-xs px-3 py-1.5 rounded-xl border border-white/10 transition">
                    🔥 Show all hot leads
                </a>
                <a href="{{ route('dealer.ai.assistant', ['query' => 'Which customers need follow-up today?']) }}" class="bg-white/10 hover:bg-white/20 text-xs px-3 py-1.5 rounded-xl border border-white/10 transition">
                    📅 Tasks & follow-ups due today
                </a>
                <a href="{{ route('dealer.ai.assistant', ['query' => 'Which leads have not been contacted for 3 days?']) }}" class="bg-white/10 hover:bg-white/20 text-xs px-3 py-1.5 rounded-xl border border-white/10 transition">
                    ⏳ Leads inactive for 3 days
                </a>
                <a href="{{ route('dealer.ai.assistant', ['query' => 'Which salesperson has the highest conversion rate?']) }}" class="bg-white/10 hover:bg-white/20 text-xs px-3 py-1.5 rounded-xl border border-white/10 transition">
                    🏆 Sales agent conversion rates
                </a>
                <a href="{{ route('dealer.ai.assistant', ['query' => 'Which products and listings generate the most leads?']) }}" class="bg-white/10 hover:bg-white/20 text-xs px-3 py-1.5 rounded-xl border border-white/10 transition">
                    📈 Top lead-generating listings
                </a>
            </div>
        </div>
    </div>

    <!-- Query Input Form -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('dealer.ai.assistant') }}" method="GET" class="space-y-4">
            <div>
                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1">Ask Your CRM Intelligence</label>
                <div class="flex gap-2">
                    <input type="text" name="query" value="{{ $query ?? '' }}" required placeholder="e.g. Which leads should my sales team call first today?" class="flex-1 text-sm border border-slate-300 rounded-xl p-3 outline-none focus:border-indigo-600">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-3 rounded-xl text-sm shadow transition flex items-center space-x-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Ask AI</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- AI Response Display -->
    @if($response)
        <div class="bg-white p-6 rounded-2xl border border-indigo-100 shadow-md space-y-3">
            <div class="flex items-center space-x-2 pb-3 border-b border-slate-100 text-xs text-indigo-700 font-bold">
                <i class="fa-solid fa-sparkles"></i>
                <span>AI Intelligence Analysis & Recommendation</span>
            </div>
            <div class="text-sm text-slate-800 leading-relaxed whitespace-pre-line font-sans">
                {{ $response }}
            </div>
            <div class="pt-3 border-t border-slate-100 text-[10px] text-slate-400 flex items-center justify-between">
                <span>Tenant Isolation: Enforced</span>
                <span>Powered by Google Gemini 1.5 Flash</span>
            </div>
        </div>
    @endif
</div>
@endsection
