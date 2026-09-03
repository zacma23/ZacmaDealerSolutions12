@extends('layouts.app')

@section('title', 'Subscribe to ' . $plan->name . ' - Zacma AI Platform')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs uppercase tracking-wider font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full">
                Dealer Membership Checkout
            </span>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900">Activate {{ $plan->name }}</h1>
        <p class="text-xs text-slate-500">Select your preferred Ethiopian or International payment method to activate your subscription immediately.</p>
    </div>

    <form action="{{ route('checkout.subscription.process', $plan->id) }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
            <!-- Left 2 Cols: Business & Gateway Selection -->
            <div class="md:col-span-2 space-y-6">
                <!-- Business & Contact Details -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">
                        1. Business & Contact Information
                    </h2>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Business / Dealership Name</label>
                        <input 
                            type="text" 
                            name="business_name" 
                            value="{{ $org?->name ?? (Auth::check() ? Auth::user()->name . ' Dealership' : old('business_name')) }}" 
                            required 
                            placeholder="e.g. Addis Prime Motors / Luxury Real Estate" 
                            class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none focus:border-emerald-600"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-700 block mb-1">Contact Person</label>
                            <input 
                                type="text" 
                                name="contact_name" 
                                value="{{ Auth::check() ? Auth::user()->name : old('contact_name') }}" 
                                required 
                                class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none focus:border-emerald-600"
                            >
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700 block mb-1">Email Address</label>
                            <input 
                                type="email" 
                                name="contact_email" 
                                value="{{ Auth::check() ? Auth::user()->email : old('contact_email') }}" 
                                required 
                                class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none focus:border-emerald-600"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Phone Number (for payment OTP/SMS)</label>
                        <input 
                            type="text" 
                            name="contact_phone" 
                            value="{{ Auth::check() ? Auth::user()->phone : old('contact_phone', '+251911000000') }}" 
                            required 
                            class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none focus:border-emerald-600"
                        >
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Select Payment Method</h2>
                        <span class="text-[11px] text-slate-400 font-medium">Instant Verification</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($providers as $prov)
                            @php
                                $provTitles = [
                                    'telebirr' => ['title' => 'Telebirr SuperApp', 'desc' => 'Pay with Ethio Telecom mobile money', 'badge' => 'ETB Mobile'],
                                    'paypal' => ['title' => 'PayPal Express', 'desc' => 'PayPal account & international debit/credit', 'badge' => 'Global USD'],
                                    'card' => ['title' => 'Stripe / Mastercard & Visa', 'desc' => 'Credit or debit card via Stripe', 'badge' => 'Cards / Stripe'],
                                    'crypto' => ['title' => 'Crypto (USDT / BTC / ETH)', 'desc' => 'Pay with USDT (TRC-20), Bitcoin, or Ethereum', 'badge' => 'Web3 / Crypto'],
                                    'santimpay' => ['title' => 'SantimPay Mobile', 'desc' => 'Direct QR & mobile banking in Ethiopia', 'badge' => 'ETB Instant'],
                                    'chapa' => ['title' => 'Chapa Pay', 'desc' => 'CBE Birr, Awash, Dashen, Bank direct', 'badge' => 'Banks & Birr'],
                                    'cash' => ['title' => 'Sandbox / Test Mode', 'desc' => 'Instant simulated test activation', 'badge' => 'Immediate Test'],
                                ];
                                $meta = $provTitles[$prov] ?? ['title' => ucfirst($prov), 'desc' => 'Integrated payment gateway', 'badge' => 'Payment'];
                            @endphp
                            <label class="border-2 border-slate-200 hover:border-emerald-600 rounded-2xl p-4 flex items-start gap-3 cursor-pointer transition has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/40">
                                <input type="radio" name="provider" value="{{ $prov }}" {{ $loop->first ? 'checked' : '' }} class="mt-1 text-emerald-600 focus:ring-emerald-500">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-900">{{ $meta['title'] }}</span>
                                        <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono font-semibold">{{ $meta['badge'] }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-1 leading-snug">{{ $meta['desc'] }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Col: Order Summary Card -->
            <div class="space-y-6">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-6 space-y-6">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Subscription Summary</span>
                        <h3 class="text-xl font-black text-slate-900 mt-1">{{ $plan->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">1 Month Dealer Membership</p>
                    </div>

                    <!-- Pricing Breakdown -->
                    <div class="border-t border-b border-slate-100 py-4 space-y-2 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Plan Fee (Monthly)</span>
                            <span class="font-bold text-slate-900">{{ number_format($plan->price) }} {{ $plan->currency }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Setup & Onboarding</span>
                            <span class="text-emerald-600 font-bold">FREE</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Tax / VAT</span>
                            <span class="text-slate-400 font-medium">Included</span>
                        </div>
                        <div class="flex justify-between text-sm font-black text-slate-900 pt-2 border-t border-slate-100">
                            <span>Total Due Now</span>
                            <span class="text-emerald-600">{{ number_format($plan->price) }} {{ $plan->currency }}</span>
                        </div>
                    </div>

                    <!-- Features Included -->
                    <div class="space-y-2 text-xs text-slate-700">
                        <div class="font-bold text-slate-900 text-[11px] uppercase tracking-wider">Features Included:</div>
                        @php $features = $plan->features ?? []; @endphp
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-600 text-xs"></i>
                            <span><strong>{{ ($features['posts_per_day'] ?? 5) < 0 ? 'Unlimited' : ($features['posts_per_day'] ?? 5) }}</strong> Items posted per day</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid {{ !empty($features['customer_phone_access']) ? 'fa-check text-emerald-600' : 'fa-lock text-slate-400' }} text-xs"></i>
                            <span>{{ !empty($features['customer_phone_access']) ? 'Full Customer Phone & WhatsApp' : 'Masked Contacts (Basic)' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-robot text-emerald-600 text-xs"></i>
                            <span>{{ !empty($features['ai_username_branding']) ? 'Dedicated @username Branded AI' : (!empty($features['ai_customer_assistant']) ? 'Storefront AI Concierge' : 'Standard AI Lead Scoring') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-600 text-xs"></i>
                            <span>Gemini Multimodal Vision Auto-Fill</span>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider rounded-2xl shadow-lg transition transform hover:scale-[1.02]"
                    >
                        Pay & Activate Plan &rarr;
                    </button>

                    <p class="text-[10px] text-center text-slate-400">
                        <i class="fa-solid fa-shield-halved mr-1 text-emerald-600"></i>
                        Encrypted 256-bit SSL transaction. Digital VAT invoice generated immediately.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
