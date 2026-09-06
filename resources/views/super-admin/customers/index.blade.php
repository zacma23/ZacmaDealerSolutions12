@extends('layouts.admin')

@section('title', 'Platform Customers Control')

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
                <span class="text-blue-600 font-semibold">Customers Directory</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-user-tag text-teal-600"></i>
                <span>Customer Base & Buyers</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Overview of registered buyers, purchase orders, vehicle bookings, and customer session inspection.</p>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Registered Customers</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">{{ number_format($totalCustomers) }}</span>
                <span class="text-[11px] text-teal-600 font-semibold">Active buyers across platform</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Customer Orders</span>
                <span class="text-2xl font-black text-blue-600 mt-1 block">{{ number_format($totalOrders) }}</span>
                <span class="text-[11px] text-slate-400">Transactions and vehicle reservations</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('super-admin.customers.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search customer by name, email, phone..." class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5">
                <i class="fa-solid fa-filter"></i>
                <span>Search</span>
            </button>
            @if(request('q'))
                <a href="{{ route('super-admin.customers.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3.5">Customer Identity</th>
                        <th class="px-4 py-3.5">Contact Phone</th>
                        <th class="px-4 py-3.5">Registered Organization</th>
                        <th class="px-4 py-3.5">Orders Placed</th>
                        <th class="px-4 py-3.5">Account Status</th>
                        <th class="px-4 py-3.5">Joined Date</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $c)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center space-x-3">
                                    <img src="{{ $c->getAvatarUrl() }}" alt="{{ $c->name }}" class="w-9 h-9 rounded-full object-cover border border-slate-200 flex-shrink-0">
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">{{ $c->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $c->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 font-semibold">
                                {{ $c->phone ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                {{ $c->organization?->name ?? 'Global Marketplace Buyer' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-black bg-blue-50 text-blue-700">
                                    {{ $c->orders_count }} orders
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $c->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $c->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-slate-400 text-[11px]">
                                {{ $c->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end space-x-1.5">
                                    <!-- View As Customer -->
                                    <form method="POST" action="{{ route('super-admin.impersonate', $c->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-lg text-xs font-black transition flex items-center space-x-1 shadow-sm" title="View Customer Portal as {{ $c->name }}">
                                            <i class="fa-solid fa-user-secret"></i>
                                            <span>View As</span>
                                        </button>
                                    </form>

                                    <!-- Toggle Active/Inactive -->
                                    <form method="POST" action="{{ route('super-admin.users.toggle', $c->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 {{ $c->is_active ? 'bg-rose-50 hover:bg-rose-100 text-rose-600' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600' }} rounded-lg text-xs font-bold transition" title="{{ $c->is_active ? 'Deactivate Account' : 'Activate Account' }}">
                                            <i class="fa-solid {{ $c->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-users text-3xl mb-2 text-slate-300 block"></i>
                                No customer accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection