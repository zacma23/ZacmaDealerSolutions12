@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-slate-900">Secure Checkout</h1>
        <p class="text-xs text-slate-500">Verified server-side payment processing powered by Zacma SaaS</p>
    </div>

    <form action="{{ route('checkout.process', $listing->id) }}" method="POST">
        @csrf
        <input type="hidden" name="purchase_type" value="{{ $purchaseType }}">
        <input type="hidden" name="amount" value="{{ $amount }}">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Order Details & Payment Selection -->
            <div class="md:col-span-2 space-y-6">
                <!-- Customer Details -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Customer Information</h2>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Full Name</label>
                        <input type="text" name="customer_name" value="{{ Auth::check() ? Auth::user()->name : old('customer_name') }}" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-700 block mb-1">Email Address</label>
                            <input type="email" name="customer_email" value="{{ Auth::check() ? Auth::user()->email : old('customer_email') }}" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700 block mb-1">Phone Number</label>
                            <input type="text" name="customer_phone" value="{{ Auth::check() ? Auth::user()->phone : old('customer_phone') }}" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                        </div>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Select Payment Gateway</h2>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($providers as $prov)
                            <label class="border-2 border-slate-200 hover:border-blue-600 rounded-xl p-3 flex flex-col items-center justify-center space-y-2 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="provider" value="{{ $prov }}" {{ $loop->first ? 'checked' : '' }} class="sr-only">
                                <span class="text-xs font-bold capitalize text-slate-800">{{ $prov }}</span>
                                <span class="text-[10px] text-slate-400">
                                    @if(in_array($prov, ['santimpay', 'telebirr', 'chapa']))
                                        Ethiopia (ETB)
                                    @elseif($prov === 'paypal' || $prov === 'card')
                                        Global / Cards
                                    @else
                                        Offline / Instant
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Order Summary Column -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">Order Summary</h3>

                    <div class="flex items-center space-x-3">
                        <img src="{{ $listing->getPrimaryImageUrl() }}" class="w-16 h-12 rounded-lg object-cover border border-slate-200">
                        <div>
                            <h4 class="font-bold text-xs text-slate-900 line-clamp-1">{{ $listing->title }}</h4>
                            <span class="text-[10px] text-slate-400 capitalize">{{ str_replace('_', ' ', $purchaseType) }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Item Price:</span>
                            <span>{{ $listing->currency }} {{ number_format($listing->price, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Payable Now:</span>
                            <span class="font-bold text-slate-900">{{ $listing->currency }} {{ number_format($amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-extrabold text-blue-600 pt-2 border-t border-slate-100">
                            <span>Total Due:</span>
                            <span>{{ $listing->currency }} {{ number_format($amount, 2) }}</span>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-sm shadow transition mt-4">
                        Pay & Complete Order
                    </button>

                    <div class="text-[10px] text-slate-400 text-center flex items-center justify-center space-x-1 pt-1">
                        <i class="fa-solid fa-lock text-[10px]"></i>
                        <span>256-bit encrypted server-verified payment</span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
