@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Customer Portal</h1>
            <p class="text-xs text-slate-500">Welcome back, {{ $user->name }} • Track your inquiries, scheduled viewings, and orders</p>
        </div>
        <a href="{{ route('marketplace.browse') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow transition">
            Explore Marketplace &rarr;
        </a>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase">My Inquiries</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ $stats['inquiries_count'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-comments"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase">Scheduled Visits</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ $stats['appointments_count'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase">Purchases & Deposits</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ $stats['orders_count'] }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
    </div>

    <!-- Inquiries & Orders Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Inquiries -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                Recent Inquiries
            </h3>
            <div class="divide-y divide-slate-100 text-xs">
                @forelse($myInquiries as $inq)
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <div class="font-bold text-slate-900">{{ $inq->listing?->title ?? 'General Inquiry' }}</div>
                            <div class="text-[11px] text-slate-400">Dealer: {{ $inq->organization?->name }} • {{ $inq->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-50 text-blue-700">
                            {{ $inq->status }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400">No active inquiries.</div>
                @endforelse
            </div>
        </div>

        <!-- Orders & Deposits -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                My Orders & Receipts
            </h3>
            <div class="divide-y divide-slate-100 text-xs">
                @forelse($myOrders as $ord)
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <div class="font-bold text-slate-900">{{ $ord->order_number }}</div>
                            <div class="text-[11px] text-slate-400">Total: {{ $ord->currency }} {{ number_format($ord->total_amount, 2) }} • {{ $ord->organization->name }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ord->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $ord->payment_status }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400">No purchases or deposits yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
