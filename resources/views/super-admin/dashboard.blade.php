@extends('layouts.admin')

@section('title', 'Super Admin Overview')

@section('content')
<div class="space-y-8">
    <!-- Top Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Organizations</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ $stats['total_organizations'] }}</span>
                <span class="text-[11px] text-emerald-600 font-semibold">{{ $stats['active_organizations'] }} active tenants</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-building"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Listings</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ $stats['total_listings'] }}</span>
                <span class="text-[11px] text-slate-400">Across all categories</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-box-archive"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Global CRM Leads</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ $stats['total_leads'] }}</span>
                <span class="text-[11px] text-amber-600 font-semibold">{{ $stats['total_contacts'] }} contacts</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-fire"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Verified Volume</span>
                <span class="text-2xl font-extrabold text-emerald-600 mt-1 block">ETB {{ number_format($stats['total_revenue']) }}</span>
                <span class="text-[11px] text-slate-400">{{ $stats['ai_requests_count'] }} AI operations</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-money-bill-trend-up"></i>
            </div>
        </div>
    </div>

    <!-- Quick Management Rows -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Organizations -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">Recent Organizations</h3>
                <a href="{{ route('super-admin.organizations.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                    Manage All <i class="fa-solid fa-arrow-right text-[10px] ml-0.5"></i>
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
                <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">System Security Audit Log</h3>
                <a href="{{ route('super-admin.audit-logs.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                    Full Log <i class="fa-solid fa-arrow-right text-[10px] ml-0.5"></i>
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
