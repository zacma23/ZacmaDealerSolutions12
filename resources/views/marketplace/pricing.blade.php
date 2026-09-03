@extends('layouts.app')

@section('title', 'Dealer Subscription Plans & Pricing')

@section('content')
<div class="py-16 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <span class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                <i class="fa-solid fa-sparkles text-blue-600 mr-1"></i> Transparent Dealer Pricing
            </span>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                Supercharge Your Dealership & Agency with AI
            </h1>
            <p class="text-base text-slate-600">
                Whether you sell vehicles, electronics, or prime real estate, Zacma AI provides the enterprise tools, customer connections, and automated intelligence to scale your business.
            </p>
        </div>

        <!-- 3 Pricing Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
            @foreach($plans as $plan)
                @php
                    $isAdvance = $plan->slug === 'dealer-advance';
                    $isPremium = $plan->slug === 'dealer-premium';
                    $features = $plan->features ?? [];
                @endphp
                <div class="bg-white rounded-3xl border {{ $isAdvance ? 'border-indigo-500 ring-2 ring-indigo-500/20 shadow-2xl' : ($isPremium ? 'border-emerald-500 shadow-xl' : 'border-slate-200 shadow-sm') }} p-8 flex flex-col justify-between relative transition-transform hover:-translate-y-1">
                    @if($isAdvance)
                        <div class="absolute -top-3.5 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-[11px] font-black uppercase tracking-wider px-4 py-1 rounded-full shadow-md">
                            ★ Advance Enterprise
                        </div>
                    @elseif($isPremium)
                        <div class="absolute -top-3.5 left-1/2 transform -translate-x-1/2 bg-emerald-600 text-white text-[11px] font-black uppercase tracking-wider px-4 py-1 rounded-full shadow-md">
                            Most Popular
                        </div>
                    @endif

                    <div>
                        <h3 class="text-xl font-black text-slate-900">{{ $plan->name }}</h3>
                        <p class="text-xs text-slate-500 mt-2 min-h-[36px]">{{ $features['description'] ?? 'Tailored subscription plan for merchants and brokers.' }}</p>

                        <div class="mt-6 mb-6">
                            <span class="text-4xl font-black text-slate-900">{{ number_format($plan->price) }}</span>
                            <span class="text-sm font-bold text-slate-500 ml-1">{{ $plan->currency }} / month</span>
                        </div>

                        <ul class="space-y-3.5 text-xs text-slate-700 border-t border-slate-100 pt-6">
                            <li class="flex items-center gap-2.5">
                                <i class="fa-solid fa-check text-emerald-600"></i>
                                <span><strong>{{ ($features['posts_per_day'] ?? 5) < 0 ? 'Unlimited' : ($features['posts_per_day'] ?? 5) }}</strong> Items posted per day</span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <i class="fa-solid {{ !empty($features['customer_phone_access']) ? 'fa-check text-emerald-600' : 'fa-lock text-slate-400' }}"></i>
                                <span>
                                    @if(!empty($features['customer_phone_access']))
                                        <strong>Full Customer Phone & WhatsApp Access</strong>
                                    @else
                                        <span class="text-slate-500">Masked Customer Contacts (Inquiry form only)</span>
                                    @endif
                                </span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <i class="fa-solid fa-robot {{ !empty($features['ai_username_branding']) || !empty($features['ai_customer_assistant']) ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                                <span>
                                    @if(!empty($features['ai_username_branding']))
                                        <strong>Dedicated AI Assistant branded with your Username</strong> (@username)
                                    @elseif(!empty($features['ai_customer_assistant']))
                                        <strong>Storefront AI Customer Concierge</strong>
                                    @else
                                        <span class="text-slate-500">Standard AI Lead Scoring</span>
                                    @endif
                                </span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <i class="fa-solid fa-camera text-emerald-600"></i>
                                <span><strong>Gemini Multimodal Vision Auto-Fill</strong></span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <i class="fa-solid fa-users text-emerald-600"></i>
                                <span><strong>{{ $plan->user_limit > 50 ? 'Unlimited' : $plan->user_limit }}</strong> Staff / Agent Seats</span>
                            </li>
                            @if(!empty($features['export_contacts_csv']))
                            <li class="flex items-center gap-2.5 text-indigo-700 font-semibold">
                                <i class="fa-solid fa-file-export text-indigo-600"></i>
                                <span>Export CRM Contacts to CSV</span>
                            </li>
                            @endif
                            @if(!empty($features['priority_marketplace']))
                            <li class="flex items-center gap-2.5 text-indigo-700 font-semibold">
                                <i class="fa-solid fa-crown text-amber-500"></i>
                                <span>Top Priority Placement in Marketplace</span>
                            </li>
                            @endif
                        </ul>
                    </div>

                    <div class="mt-8">
                        @auth
                            <a href="{{ route('dealer.subscription.index') }}" class="block text-center w-full py-3.5 {{ $isAdvance ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : ($isPremium ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-slate-900 hover:bg-slate-800 text-white') }} rounded-xl font-bold text-xs shadow-lg transition">
                                Choose {{ $plan->name }} &rarr;
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="block text-center w-full py-3.5 {{ $isAdvance ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : ($isPremium ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-slate-900 hover:bg-slate-800 text-white') }} rounded-xl font-bold text-xs shadow-lg transition">
                                Register as Dealer &rarr;
                            </a>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Payment Gateway Logos -->
        <div class="bg-white border border-slate-200 rounded-3xl p-8 text-center space-y-4">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Accepted Ethiopian & Global Payment Gateways</h3>
            <div class="flex flex-wrap items-center justify-center gap-4 text-xs font-bold text-slate-700">
                <div class="px-4 py-2 bg-slate-100 rounded-xl">Telebirr SuperApp</div>
                <div class="px-4 py-2 bg-slate-100 rounded-xl">SantimPay Mobile</div>
                <div class="px-4 py-2 bg-slate-100 rounded-xl">Chapa (CBE Birr / Awash / Dashen)</div>
                <div class="px-4 py-2 bg-slate-100 rounded-xl">PayPal / Debit & Credit Cards</div>
            </div>
            <p class="text-xs text-slate-500">Instant digital activation upon payment verification with downloadable VAT-compliant invoices.</p>
        </div>
    </div>
</div>
@endsection
