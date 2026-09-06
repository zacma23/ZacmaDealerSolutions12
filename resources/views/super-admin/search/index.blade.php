@extends('layouts.admin')

@section('title', 'Global Platform Search')

@section('content')
<div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('super-admin.dashboard') }}" class="hover:text-blue-600">Super Admin</a>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-slate-700 font-semibold">Command Center</span>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-blue-600 font-semibold">Platform Global Search</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass text-blue-600"></i>
                <span>Unified Platform Search</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Instant multi-entity search across listings, users, organizations, CRM leads, and orders.</p>
        </div>
    </div>

    <!-- Search Form -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('super-admin.search') }}" class="flex gap-3">
            <div class="flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="q" value="{{ $q }}" placeholder="Search across all listings, users, emails, dealerships, leads, and orders..." class="w-full pl-11 pr-4 py-3 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-medium" autofocus>
            </div>
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-black transition flex items-center space-x-2 shadow-sm">
                <i class="fa-solid fa-search"></i>
                <span>Search Platform</span>
            </button>
        </form>
    </div>

    @if($q !== '')
        <!-- Search Summary Bar -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex flex-wrap items-center justify-between gap-3 text-xs text-blue-900">
            <div>
                Search results for: <span class="font-bold font-mono">"{{ $q }}"</span>
            </div>
            <div class="flex items-center space-x-3 font-semibold">
                <span>{{ $results['listings']->count() }} listings</span>
                <span>&bull;</span>
                <span>{{ $results['users']->count() }} users</span>
                <span>&bull;</span>
                <span>{{ $results['organizations']->count() }} organizations</span>
                <span>&bull;</span>
                <span>{{ $results['leads']->count() }} leads</span>
                <span>&bull;</span>
                <span>{{ $results['orders']->count() }} orders</span>
            </div>
        </div>

        <div class="space-y-8">
            <!-- 1. Matching Listings -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="font-bold text-xs uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-box-archive text-blue-600"></i>
                        <span>Matching Listings ({{ $results['listings']->count() }})</span>
                    </h2>
                    <a href="{{ route('super-admin.listings.index', ['q' => $q]) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                        View All Listings &rarr;
                    </a>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($results['listings'] as $l)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold flex-shrink-0">
                                    <i class="fa-solid fa-tag"></i>
                                </div>
                                <div>
                                    <a href="{{ route('marketplace.show', $l->slug) }}" target="_blank" class="font-bold text-slate-900 hover:text-blue-600 block text-xs">
                                        {{ $l->title }}
                                    </a>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $l->category?->name }} &bull; Dealer: {{ $l->organization?->name }} &bull; <span class="font-bold text-blue-600">{{ $l->currency }} {{ number_format($l->price, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $l->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $l->status }}
                                </span>
                                <a href="{{ route('marketplace.show', $l->slug) }}" target="_blank" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold" title="Preview Public View">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">No listings found matching "{{ $q }}".</div>
                    @endforelse
                </div>
            </div>

            <!-- 2. Matching Users & Accounts -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="font-bold text-xs uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-users text-purple-600"></i>
                        <span>Matching Users & Accounts ({{ $results['users']->count() }})</span>
                    </h2>
                    <a href="{{ route('super-admin.users.index', ['q' => $q]) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                        View All Users &rarr;
                    </a>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($results['users'] as $u)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $u->getAvatarUrl() }}" alt="{{ $u->name }}" class="w-10 h-10 rounded-full object-cover border border-slate-200 flex-shrink-0">
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ $u->name }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $u->email }} &bull; Phone: {{ $u->phone ?? '—' }} &bull; {{ $u->organization?->name ?? 'Global' }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-50 text-purple-700 border border-purple-200">
                                    {{ $u->role }}
                                </span>
                                @if($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('super-admin.impersonate', $u->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-lg text-xs font-black transition flex items-center space-x-1 shadow-sm">
                                            <i class="fa-solid fa-user-secret"></i>
                                            <span>View As</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">No users found matching "{{ $q }}".</div>
                    @endforelse
                </div>
            </div>

            <!-- 3. Matching Organizations / Dealerships -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="font-bold text-xs uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-building text-blue-600"></i>
                        <span>Matching Dealerships & Organizations ({{ $results['organizations']->count() }})</span>
                    </h2>
                    <a href="{{ route('super-admin.organizations.index', ['q' => $q]) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                        View All Organizations &rarr;
                    </a>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($results['organizations'] as $org)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-800 flex items-center justify-center font-black flex-shrink-0">
                                    {{ strtoupper(substr($org->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ $org->name }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $org->city }}, {{ $org->country }} &bull; {{ $org->email }}
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $org->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $org->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">No organizations found matching "{{ $q }}".</div>
                    @endforelse
                </div>
            </div>

            <!-- 4. Matching CRM Leads -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="font-bold text-xs uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-fire text-amber-500"></i>
                        <span>Matching CRM Leads ({{ $results['leads']->count() }})</span>
                    </h2>
                    <a href="{{ route('super-admin.crm.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">
                        View CRM Hub &rarr;
                    </a>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($results['leads'] as $ld)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                            <div>
                                <div class="font-bold text-slate-900 text-xs">{{ $ld->title }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    Contact: {{ $ld->contact?->first_name }} {{ $ld->contact?->last_name }} &bull; Organization: {{ $ld->organization?->name }}
                                </div>
                            </div>
                            <div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-50 text-amber-800">
                                    {{ $ld->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">No CRM leads found matching "{{ $q }}".</div>
                    @endforelse
                </div>
            </div>

            <!-- 5. Matching Orders -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="font-bold text-xs uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-blue-600"></i>
                        <span>Matching Orders ({{ $results['orders']->count() }})</span>
                    </h2>
                    <a href="{{ route('super-admin.orders.index', ['q' => $q]) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                        View All Orders &rarr;
                    </a>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($results['orders'] as $ord)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                            <div>
                                <div class="font-mono font-bold text-slate-900 text-xs">#{{ $ord->order_number ?? $ord->id }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    Customer: {{ $ord->customer_name }} &bull; Total: <span class="font-bold text-slate-900">{{ $ord->currency }} {{ number_format($ord->total_amount, 2) }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $ord->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                    {{ $ord->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs">No orders found matching "{{ $q }}".</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
@endsection