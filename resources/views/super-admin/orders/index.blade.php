@extends('layouts.admin')

@section('title', 'Global Platform Orders & Purchases')

@section('content')
<div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('super-admin.dashboard') }}" class="hover:text-blue-600">Super Admin</a>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-slate-700 font-semibold">Sales & Commerce</span>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-blue-600 font-semibold">Platform Orders</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-receipt text-blue-600"></i>
                <span>Platform Orders & Bookings</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Global transaction audit log across all dealerships, real estate agencies, and customer purchases.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('super-admin.payments.index') }}" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-xl text-xs font-bold transition flex items-center space-x-2 border border-emerald-200">
                <i class="fa-solid fa-credit-card"></i>
                <span>View Payment Gateways &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Recorded Orders</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">{{ number_format($orders->total()) }}</span>
                <span class="text-[11px] text-slate-400">Across all platform tenants</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Paid Orders Volume</span>
                <span class="text-2xl font-black text-emerald-600 mt-1 block">ETB {{ number_format($totalRevenue, 2) }}</span>
                <span class="text-[11px] text-emerald-600 font-semibold">Completed customer purchases</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('super-admin.orders.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by order #, customer name, email..." class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <div class="sm:w-48">
                <select name="status" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700">
                    <option value="">All Order Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5">
                <i class="fa-solid fa-filter"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['q', 'status']))
                <a href="{{ route('super-admin.orders.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3.5">Order Number</th>
                        <th class="px-4 py-3.5">Customer</th>
                        <th class="px-4 py-3.5">Organization / Dealership</th>
                        <th class="px-4 py-3.5">Item / Listing</th>
                        <th class="px-4 py-3.5">Amount</th>
                        <th class="px-4 py-3.5">Payment</th>
                        <th class="px-4 py-3.5">Order Status</th>
                        <th class="px-4 py-3.5">Created At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $o)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3.5 font-mono font-bold text-slate-900">
                                #{{ $o->order_number ?? $o->id }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-800">{{ $o->customer_name ?? $o->user?->name ?? 'Guest Buyer' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $o->customer_email ?? $o->user?->email }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-semibold text-slate-800">{{ $o->organization?->name ?? 'Global Platform' }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($o->listing)
                                    <a href="{{ route('marketplace.show', $o->listing->slug) }}" target="_blank" class="font-bold text-blue-600 hover:underline line-clamp-1">
                                        {{ $o->listing->title }}
                                    </a>
                                @else
                                    <span class="text-slate-500 italic">Custom Order</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-black text-slate-900">
                                {{ $o->currency }} {{ number_format($o->total_amount, 2) }}
                            </td>
                            <td class="px-4 py-3.5">
                                @php
                                    $payBadge = [
                                        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'refunded' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $payBadge[$o->payment_status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $o->payment_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $o->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($o->status === 'confirmed' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ $o->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-slate-400 text-[11px]">
                                {{ $o->created_at->format('M d, Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300 block"></i>
                                No orders found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection