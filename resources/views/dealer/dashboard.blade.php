@extends('layouts.admin')

@section('title', 'Dealership CRM Overview')

@section('content')
<div class="space-y-8">
    <!-- Header Greeting & AI CTA -->
    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-left">
            <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-blue-100">
                <i class="fa-solid fa-sparkles text-amber-300"></i>
                <span>AI CRM Intelligence Active</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Welcome back, {{ Auth::user()->name }}</h2>
            <p class="text-xs sm:text-sm text-blue-100 max-w-xl">
                Here is your dealership snapshot: {{ $stats['new_leads'] }} new leads require response today, and {{ $stats['follow_ups_due'] }} follow-up tasks are scheduled.
            </p>
        </div>
        <a href="{{ route('dealer.ai.assistant') }}" class="bg-white text-blue-800 hover:bg-blue-50 font-bold px-5 py-3 rounded-2xl text-xs shadow-md transition flex items-center space-x-2 flex-shrink-0">
            <i class="fa-solid fa-robot"></i>
            <span>Ask AI CRM Assistant</span>
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Active Leads</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ $stats['total_leads'] }}</span>
                <span class="text-[11px] text-rose-600 font-bold flex items-center space-x-1">
                    <i class="fa-solid fa-fire text-[10px]"></i>
                    <span>{{ $stats['hot_leads'] }} High-Intent Hot Leads</span>
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-fire"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Pipeline Value</span>
                <span class="text-2xl font-extrabold text-blue-600 mt-1 block">ETB {{ number_format($stats['pipeline_value']) }}</span>
                <span class="text-[11px] text-slate-400">{{ $stats['open_deals_count'] }} open deals</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-columns"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Tasks Due Today</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ $stats['follow_ups_due'] }}</span>
                <a href="{{ route('dealer.crm.tasks.index', ['filter' => 'today']) }}" class="text-[11px] text-blue-600 font-semibold hover:underline">View queue &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-list-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Published Inventory</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ $stats['total_listings'] }}</span>
                <a href="{{ route('dealer.listings.create') }}" class="text-[11px] text-emerald-600 font-semibold hover:underline">+ Add listing</a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-box-archive"></i>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Split: Recent Leads & Timeline -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Leads & Opportunities -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Recent Inquiries & Leads</h3>
                <a href="{{ route('dealer.crm.leads.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                    View All Leads &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentLeads as $l)
                    <div class="py-3.5 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-800">
                                {{ strtoupper(substr($l->contact?->first_name ?? 'L', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 text-sm">
                                    <a href="{{ route('dealer.crm.contacts.360', $l->contact_id) }}" class="hover:text-blue-600">
                                        {{ $l->contact?->full_name }}
                                    </a>
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    Listing: <strong>{{ $l->listing?->title ?? 'General Inquiry' }}</strong> • Source: {{ $l->source }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $l->score_category === 'hot' ? 'bg-rose-50 text-rose-700' : ($l->score_category === 'warm' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                {{ $l->score }}/100 {{ $l->score_category }}
                            </span>
                            <a href="{{ route('dealer.crm.contacts.360', $l->contact_id) }}" class="px-3 py-1 bg-slate-100 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-semibold transition">
                                360 Profile
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400">No recent leads.</div>
                @endforelse
            </div>
        </div>

        <!-- Activity Stream -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Activity Timeline</h3>
            </div>

            <div class="space-y-4 text-xs">
                @forelse($recentActivities as $act)
                    <div class="flex space-x-3">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                            <i class="{{ $act->type === 'call' ? 'fa-solid fa-phone' : ($act->type === 'payment' ? 'fa-solid fa-credit-card' : 'fa-solid fa-comment-dots') }}"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-bold text-slate-900">{{ $act->subject }}</div>
                            <p class="text-slate-500 text-[11px] line-clamp-2 mt-0.5">{{ $act->description }}</p>
                            <span class="text-[10px] text-slate-400 block mt-1">{{ $act->occurred_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400">No recent activity logged.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
