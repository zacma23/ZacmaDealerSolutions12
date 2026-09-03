@extends('layouts.admin')

@section('title', 'Orders & Payment Transactions')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Orders & Invoices</h2>
        <p class="text-xs text-slate-500">Track paid transactions, vehicle reservation deposits, and invoice receipts</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3">Order Number</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Listing Item</th>
                        <th class="px-4 py-3">Total Amount</th>
                        <th class="px-4 py-3">Payment Status</th>
                        <th class="px-4 py-3">Order Status</th>
                        <th class="px-4 py-3 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $o)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3 font-mono font-bold text-blue-600">{{ $o->order_number }}</td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $o->customer_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $o->customer_phone }}</div>
                            </td>
                            <td class="px-4 py-3 truncate max-w-[160px] font-medium">{{ $o->listing?->title ?? 'Direct Order' }}</td>
                            <td class="px-4 py-3 font-extrabold text-slate-900">{{ $o->currency }} {{ number_format($o->total_amount, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $o->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $o->payment_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 capitalize font-semibold">{{ $o->status }}</td>
                            <td class="px-4 py-3 text-right text-slate-400 text-[11px]">{{ $o->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">No orders or payments processed yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
