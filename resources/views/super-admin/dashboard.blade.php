@extends('layouts.admin')

@section('title', 'Super Admin Command Center')

@section('content')
<div class="space-y-8">
    <!-- Top Command Header & Quick Search -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-blue-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-3xl">
            <span class="px-3 py-1 bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-bold rounded-full uppercase tracking-wider inline-block mb-3">
                <i class="fa-solid fa-crown mr-1 text-amber-400"></i> Platform Central Command
            </span>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                Zacma Platform Command Center
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                Centralized management and unified oversight across Automotive, Real Estate, Electronics, Sales Agents, Dealers, CRM Leads, and Financial Transactions.
            </p>

            <!-- Global Search Box -->
            <form method="GET" action="{{ route('super-admin.search') }}" class="mt-6 flex items-center bg-white/10 backdrop-blur-md p-1.5 rounded-2xl border border-white/20 shadow-inner">
                <div class="flex-1 flex items-center pl-3">
                    <i class="fa-solid fa-magnifying-glass text-slate-300 text-sm mr-2.5"></i>
                    <input type="text" name="q" placeholder="Global search: vehicles, properties, electronics, dealers, users, leads, orders..." class="bg-transparent border-0 outline-none text-white placeholder-slate-400 text-xs sm:text-sm w-full font-medium">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow-md">
                    <span>Search</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </form>
        </div>

        <div class="absolute right-0 bottom-0 translate-x-10 translate-y-10 opacity-10 pointer-events-none hidden lg:block">
            <i class="fa-solid fa-network-wired text-[240px]"></i>
        </div>
    </div>

    <!-- Primary Clickable Metrics Cards -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-500">Core Command Metrics (Click to Manage)</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Organizations / Dealers -->
            <a href="{{ route('super-admin.organizations.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-400 transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block group-hover:text-blue-600 transition">Dealerships & Tenants</span>
                        <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $stats['total_organizations'] }}</span>
                        <span class="text-[11px] text-emerald-600 font-semibold">{{ $stats['active_organizations'] }} active businesses</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
                        <i class="fa-solid fa-building"></i>
                    </div>
                </div>
            </a>

            <!-- Total Listings -->
            <a href="{{ route('super-admin.listings.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-400 transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block group-hover:text-indigo-600 transition">Global Inventory</span>
                        <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $stats['total_listings'] }}</span>
                        <span class="text-[11px] text-indigo-600 font-semibold">{{ $stats['active_listings'] }} published listings</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                </div>
            </a>

            <!-- CRM Leads -->
            <a href="{{ route('super-admin.crm.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-amber-400 transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block group-hover:text-amber-600 transition">Global CRM Leads</span>
                        <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $stats['total_leads'] }}</span>
                        <span class="text-[11px] text-amber-600 font-semibold">{{ $stats['total_contacts'] }} customer contacts</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
                        <i class="fa-solid fa-fire"></i>
                    </div>
                </div>
            </a>

            <!-- Verified Revenue -->
            <a href="{{ route('super-admin.payments.index') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-400 transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block group-hover:text-emerald-600 transition">Verified Revenue</span>
                        <span class="text-2xl font-black text-emerald-600 mt-1 block">ETB {{ number_format($stats['total_revenue']) }}</span>
                        <span class="text-[11px] text-slate-400">{{ $stats['ai_requests_count'] }} AI operations</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
                        <i class="fa-solid fa-money-bill-trend-up"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Industry Sectors & Accounts Control Grid -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-500">Industry Sectors & Account Command</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <!-- Auto Sector -->
            <a href="{{ route('super-admin.auto.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-blue-500 hover:shadow-md transition text-center group">
                <div class="w-10 h-10 mx-auto rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition">
                    <i class="fa-solid fa-car"></i>
                </div>
                <div class="text-xs font-bold text-slate-900">Auto & Vehicles</div>
                <div class="text-[11px] text-blue-600 font-bold mt-0.5">{{ $stats['auto_listings'] }} vehicles</div>
            </a>

            <!-- Real Estate Sector -->
            <a href="{{ route('super-admin.property.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-500 hover:shadow-md transition text-center group">
                <div class="w-10 h-10 mx-auto rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div class="text-xs font-bold text-slate-900">Real Estate</div>
                <div class="text-[11px] text-emerald-600 font-bold mt-0.5">{{ $stats['property_listings'] }} properties</div>
            </a>

            <!-- Electronics Sector -->
            <a href="{{ route('super-admin.electronics.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-indigo-500 hover:shadow-md transition text-center group">
                <div class="w-10 h-10 mx-auto rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition">
                    <i class="fa-solid fa-laptop"></i>
                </div>
                <div class="text-xs font-bold text-slate-900">Electronics</div>
                <div class="text-[11px] text-indigo-600 font-bold mt-0.5">{{ $stats['electronics_listings'] }} devices</div>
            </a>

            <!-- Customers -->
            <a href="{{ route('super-admin.customers.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-teal-500 hover:shadow-md transition text-center group">
                <div class="w-10 h-10 mx-auto rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="text-xs font-bold text-slate-900">Customers</div>
                <div class="text-[11px] text-teal-600 font-bold mt-0.5">{{ $stats['total_customers'] }} buyers</div>
            </a>

            <!-- Sales Agents -->
            <a href="{{ route('super-admin.sales-agents.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-amber-500 hover:shadow-md transition text-center group">
                <div class="w-10 h-10 mx-auto rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition">
                    <i class="fa-solid fa-id-badge"></i>
                </div>
                <div class="text-xs font-bold text-slate-900">Sales Agents</div>
                <div class="text-[11px] text-amber-600 font-bold mt-0.5">{{ $stats['total_sales_agents'] }} agents</div>
            </a>

            <!-- Orders & Purchases -->
            <a href="{{ route('super-admin.orders.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-rose-500 hover:shadow-md transition text-center group">
                <div class="w-10 h-10 mx-auto rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg mb-2 group-hover:scale-110 transition">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div class="text-xs font-bold text-slate-900">Platform Orders</div>
                <div class="text-[11px] text-rose-600 font-bold mt-0.5">{{ $stats['total_orders'] }} orders</div>
            </a>
        </div>
    </div>

    <!-- Management Grids (Recent Listings, Recent Leads, Recent Organizations, Audit Log) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Listings Moderation -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-box-archive text-blue-600"></i>
                    <span>Recent Listings Stream</span>
                </h3>
                <a href="{{ route('super-admin.listings.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                    Moderate All &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentListings as $l)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <a href="{{ route('marketplace.show', $l->slug) }}" target="_blank" class="font-bold text-slate-900 hover:text-blue-600 block line-clamp-1">
                                {{ $l->title }}
                            </a>
                            <span class="text-slate-400 text-[11px]">
                                {{ $l->category?->name }} &bull; {{ $l->organization?->name }} &bull; <span class="font-bold text-blue-600">{{ $l->currency }} {{ number_format($l->price) }}</span>
                            </span>
                        </div>
                        <div class="flex items-center space-x-2 flex-shrink-0">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $l->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $l->status }}
                            </span>
                            @if(($l->approval_status ?? '') !== 'approved')
                                <form method="POST" action="{{ route('super-admin.listings.approve', $l->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded text-xs" title="Approve">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400">No listings published recently.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent CRM Leads -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-fire text-amber-500"></i>
                    <span>Recent CRM Leads</span>
                </h3>
                <a href="{{ route('super-admin.crm.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                    CRM Hub &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentLeads as $lead)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 block line-clamp-1">{{ $lead->title }}</span>
                            <span class="text-slate-400 text-[11px]">
                                Contact: {{ $lead->contact?->first_name }} {{ $lead->contact?->last_name }} &bull; {{ $lead->organization?->name }}
                            </span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-50 text-amber-800">
                            {{ $lead->status }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400">No CRM leads recorded yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Organizations -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-building text-blue-600"></i>
                    <span>Recent Dealerships & Organizations</span>
                </h3>
                <a href="{{ route('super-admin.organizations.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                    Manage All &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentOrganizations as $org)
                    <div class="py-3 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-700">
                                {{ strtoupper(substr($org->name, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900">{{ $org->name }}</h4>
                                <span class="text-slate-400 text-[11px]">{{ $org->city }}, {{ $org->country }} • Plan: {{ $org->plan?->name ?? 'Free' }}</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $org->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                            {{ $org->status }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400">No organizations found.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Audit Log Stream -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-purple-600"></i>
                    <span>Platform Security Audit Stream</span>
                </h3>
                <a href="{{ route('super-admin.audit-logs.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                    Full Log &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($recentLogs as $log)
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <span class="font-mono font-semibold text-slate-800">{{ $log->action }}</span>
                            <span class="text-slate-400 text-[11px] block">
                                User: {{ $log->user?->name ?? 'System' }} • IP: {{ $log->ip_address }}
                            </span>
                        </div>
                        <span class="text-[11px] text-slate-400">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400">No audit events logged.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection