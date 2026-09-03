@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto px-4 py-16 text-center">
    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
        <i class="fa-solid fa-check"></i>
    </div>
    <h1 class="text-2xl font-extrabold text-slate-900 mb-2">Payment Confirmed!</h1>
    <p class="text-xs text-slate-500 mb-6">
        Your order has been verified and processed by the dealer. An invoice receipt has been created.
    </p>

    <div class="bg-white p-6 rounded-2xl border border-slate-200 text-left space-y-3 shadow-sm mb-6 text-xs">
        <div class="flex justify-between py-1 border-b border-slate-100">
            <span class="text-slate-500">Transaction Reference:</span>
            <span class="font-mono font-bold text-slate-800">{{ $reference }}</span>
        </div>
        @if($payment && $payment->order)
            <div class="flex justify-between py-1 border-b border-slate-100">
                <span class="text-slate-500">Order Number:</span>
                <span class="font-bold text-slate-800">{{ $payment->order->order_number }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-100">
                <span class="text-slate-500">Amount Paid:</span>
                <span class="font-bold text-emerald-600">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="flex justify-between py-1">
                <span class="text-slate-500">Merchant:</span>
                <span class="font-semibold text-slate-800">{{ $payment->organization->name }}</span>
            </div>
        @endif
    </div>

    <div class="flex justify-center space-x-3">
        <a href="{{ route('home') }}" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800">
            Return to Marketplace
        </a>
        @auth
            <a href="{{ route('customer.dashboard') }}" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-semibold hover:bg-blue-700">
                View My Orders
            </a>
        @endauth
    </div>
</div>
@endsection
