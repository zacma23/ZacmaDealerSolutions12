@extends('layouts.admin')

@section('title', 'Platform Payments & Transactions')

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
                <span class="text-blue-600 font-semibold">Payments & Gateways</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-credit-card text-emerald-600"></i>
                <span>Payments & Gateway Verification</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Real-time payment logs across SantimPay, Telebirr, Chapa, Stripe, PayPal, and manual bank deposits.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('super-admin.settings.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center space-x-2">
                <i class="fa-solid fa-sliders"></i>
                <span>Gateway API Keys</span>
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Verified Revenue</span>
                <span class="text-2xl font-black text-emerald-600 mt-1 block">ETB {{ number_format($totalVerified, 2) }}</span>
                <span class="text-[11px] text-emerald-600 font-semibold">Successfully cleared payments</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Transactions</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">{{ number_format($payments->total()) }}</span>
                <span class="text-[11px] text-slate-400">All payment records logged</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-money-bill-trend-up"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('super-admin.payments.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="sm:w-48">
                <select name="provider" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700">
                    <option value="">All Providers</option>
                    <option value="santimpay" {{ request('provider') === 'santimpay' ? 'selected' : '' }}>SantimPay</option>
                    <option value="telebirr" {{ request('provider') === 'telebirr' ? 'selected' : '' }}>Telebirr</option>
                    <option value="chapa" {{ request('provider') === 'chapa' ? 'selected' : '' }}>Chapa</option>
                    <option value="paypal" {{ request('provider') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                    <option value="stripe" {{ request('provider') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                    <option value="manual" {{ request('provider') === 'manual' ? 'selected' : '' }}>Manual / Bank</option>
                </select>
            </div>
            <div class="sm:w-48">
                <select name="status" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700">
                    <option value="">All Statuses</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5">
                <i class="fa-solid fa-filter"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['provider', 'status']))
                <a href="{{ route('super-admin.payments.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3.5">Transaction Ref</th>
                        <th class="px-4 py-3.5">Gateway Provider</th>
                        <th class="px-4 py-3.5">Organization / Dealership</th>
                        <th class="px-4 py-3.5">Amount</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Order Ref</th>
                        <th class="px-4 py-3.5">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $pm)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3.5 font-mono font-bold text-slate-900">
                                {{ $pm->transaction_reference ?? 'TX-' . $pm->id }}
                            </td>
                            <td class="px-4 py-3.5">
                                @php
                                    $provBadge = [
                                        'santimpay' => 'bg-emerald-100 text-emerald-800',
                                        'telebirr' => 'bg-blue-100 text-blue-800',
                                        'chapa' => 'bg-teal-100 text-teal-800',
                                        'paypal' => 'bg-indigo-100 text-indigo-800',
                                        'stripe' => 'bg-purple-100 text-purple-800',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase {{ $provBadge[$pm->provider] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $pm->provider }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-semibold text-slate-800">{{ $pm->organization?->name ?? 'Global Platform' }}</span>
                            </td>
                            <td class="px-4 py-3.5 font-black text-emerald-600">
                                {{ $pm->currency }} {{ number_format($pm->amount, 2) }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $pm->status === 'verified' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($pm->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                    {{ $pm->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-500">
                                {{ $pm->order ? '#' . ($pm->order->order_number ?? $pm->order->id) : 'Direct Subscription' }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-400 text-[11px]">
                                {{ $pm->created_at->format('M d, Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-credit-card text-3xl mb-2 text-slate-300 block"></i>
                                No payment transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection