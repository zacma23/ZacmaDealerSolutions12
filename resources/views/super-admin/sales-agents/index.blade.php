@extends('layouts.admin')

@section('title', 'Sales Agents & CRM Representatives')

@section('content')
<div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('super-admin.dashboard') }}" class="hover:text-blue-600">Super Admin</a>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-slate-700 font-semibold">Accounts</span>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-blue-600 font-semibold">Sales Agents</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-id-badge text-amber-500"></i>
                <span>Sales Agents & Deal Managers</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Direct oversight of sales representatives, assigned leads, closing pipelines, and agent session preview.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('super-admin.crm.index') }}" class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-xl text-xs font-bold transition flex items-center space-x-2 border border-amber-200">
                <i class="fa-solid fa-fire"></i>
                <span>Global CRM Pipeline &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Sales Agents</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">{{ number_format($totalAgents) }}</span>
                <span class="text-[11px] text-amber-600 font-semibold">Across all dealerships & agencies</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-tie"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Role Assignment</span>
                <span class="text-base font-black text-slate-900 mt-1 block">SALES_AGENT</span>
                <span class="text-[11px] text-slate-400">Can manage CRM leads & pipeline deals</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('super-admin.sales-agents.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search agent by name or email..." class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-black transition flex items-center justify-center space-x-1.5">
                <i class="fa-solid fa-filter"></i>
                <span>Search</span>
            </button>
            @if(request('q'))
                <a href="{{ route('super-admin.sales-agents.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Sales Agents Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3.5">Agent Details</th>
                        <th class="px-4 py-3.5">Dealership / Tenant</th>
                        <th class="px-4 py-3.5">Assigned Leads</th>
                        <th class="px-4 py-3.5">Active Deals</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Joined</th>
                        <th class="px-4 py-3.5 text-right">Actions & Preview</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($agents as $a)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center space-x-3">
                                    <img src="{{ $a->getAvatarUrl() }}" alt="{{ $a->name }}" class="w-9 h-9 rounded-full object-cover border border-slate-200 flex-shrink-0">
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">{{ $a->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $a->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($a->organization)
                                    <div class="font-bold text-slate-800">{{ $a->organization->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $a->organization->city }}, {{ $a->organization->country }}</div>
                                @else
                                    <span class="text-slate-400 italic">No Dealership</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-black bg-amber-50 text-amber-800 border border-amber-200">
                                    {{ $a->assigned_leads_count }} leads
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-black bg-blue-50 text-blue-800 border border-blue-200">
                                    {{ $a->assigned_deals_count }} deals
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $a->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $a->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-slate-400 text-[11px]">
                                {{ $a->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end space-x-1.5">
                                    <!-- View As Sales Agent -->
                                    <form method="POST" action="{{ route('super-admin.impersonate', $a->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-lg text-xs font-black transition flex items-center space-x-1 shadow-sm" title="Impersonate & View CRM Portal as {{ $a->name }}">
                                            <i class="fa-solid fa-user-secret"></i>
                                            <span>View As</span>
                                        </button>
                                    </form>

                                    <!-- Toggle Active/Inactive -->
                                    <form method="POST" action="{{ route('super-admin.users.toggle', $a->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 {{ $a->is_active ? 'bg-rose-50 hover:bg-rose-100 text-rose-600' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600' }} rounded-lg text-xs font-bold transition" title="{{ $a->is_active ? 'Deactivate Account' : 'Activate Account' }}">
                                            <i class="fa-solid {{ $a->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-user-tie text-3xl mb-2 text-slate-300 block"></i>
                                No sales agents found matching your query.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $agents->links() }}
        </div>
    </div>
</div>
@endsection