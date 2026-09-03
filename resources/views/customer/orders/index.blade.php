@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">My Orders & Receipts</h1>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3">Order Number</th>
                        <th class="px-4 py-3">Merchant / Dealer</th>
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
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $o->organization->name }}</td>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $o->currency }} {{ number_format($o->total_amount, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $o->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $o->payment_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 capitalize font-semibold">{{ $o->status }}</td>
                            <td class="px-4 py-3 text-right text-slate-400 text-[11px]">{{ $o->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No orders placed yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
