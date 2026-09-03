@extends('layouts.admin')

@section('title', 'Subscription Plans & Quotas')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Dealer Subscription & Quotas</h2>
            <p class="text-xs text-slate-500 mt-0.5">Scale your dealership across vehicles, electronics, or real estate with tailored daily posting limits and AI features.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl font-semibold">
                Current Plan: <span class="text-emerald-600 font-bold">{{ $org?->plan?->name ?? 'No Plan Active' }}</span>
            </span>
        </div>
    </div>

    <!-- Active Subscription Status Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 rounded-2xl p-6 text-white border border-slate-700 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="text-xs uppercase tracking-wider font-semibold text-emerald-400">Active Membership</span>
                <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] px-2 py-0.5 rounded-full font-semibold">
                    {{ $org?->isActive() ? 'Active' : 'Pending' }}
                </span>
            </div>
            <h3 class="text-xl font-black text-white">{{ $org?->plan?->name ?? 'Dealer Membership' }}</h3>
            <p class="text-xs text-slate-400">
                Renews: {{ $org?->subscription_ends_at ? $org->subscription_ends_at->format('M d, Y') : 'Active (Demo)' }} • Multi-Tenant Scoped
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 border-t md:border-t-0 md:border-l border-slate-700 md:pl-6">
            <div>
                <div class="text-[11px] text-slate-400 font-medium">Daily Post Limit</div>
                <div class="text-lg font-bold text-white mt-0.5">
                    {{ $org ? ($org->dailyPostsAllowed() < 0 ? 'Unlimited' : $org->dailyPostsAllowed() . ' / day') : '5 / day' }}
                </div>
                <div class="text-[10px] text-emerald-400">{{ $postsToday }} posted today</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 font-medium">Customer Phone</div>
                <div class="text-lg font-bold text-white mt-0.5">
                    {{ $org?->canAccessCustomerPhone() ? 'Full Access' : 'Masked (Basic)' }}
                </div>
                <div class="text-[10px] text-slate-400">{{ $org?->canAccessCustomerPhone() ? 'Direct WhatsApp' : 'Inquiries Only' }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-400 font-medium">AI Assistant</div>
                <div class="text-lg font-bold text-white mt-0.5">
                    {{ $org?->hasUsernameBrandedAi() ? '@' . ($org->subdomain ?: $org->slug) : ($org?->hasAiCustomerAssistant() ? 'Storefront AI' : 'Basic AI') }}
                </div>
                <div class="text-[10px] text-emerald-400">{{ $org?->hasUsernameBrandedAi() ? 'Branded Concierge' : 'Standard' }}</div>
            </div>
        </div>
    </div>

    <!-- 3 Plan Pricing Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            @php
                $isCurrent = $org && $org->subscription_plan_id == $plan->id;
                $isAdvance = $plan->slug === 'dealer-advance';
                $isPremium = $plan->slug === 'dealer-premium';
                $features = $plan->features ?? [];
            @endphp
            <div class="bg-white rounded-3xl border {{ $isAdvance ? 'border-indigo-500 ring-2 ring-indigo-500/20 shadow-2xl' : ($isPremium ? 'border-emerald-500 shadow-xl' : 'border-slate-200 shadow-sm') }} flex flex-col p-6 relative transition-all duration-200 hover:-translate-y-1">
                @if($isAdvance)
                    <div class="absolute -top-3.5 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-[11px] font-black uppercase tracking-wider px-3.5 py-1 rounded-full shadow-md">
                        ★ Advance Enterprise
                    </div>
                @elseif($isPremium)
                    <div class="absolute -top-3.5 left-1/2 transform -translate-x-1/2 bg-emerald-600 text-white text-[11px] font-black uppercase tracking-wider px-3.5 py-1 rounded-full shadow-md">
                        Most Popular
                    </div>
                @endif

                <div class="mb-5 pt-2">
                    <h4 class="text-lg font-black text-slate-900">{{ $plan->name }}</h4>
                    <p class="text-xs text-slate-500 mt-1 min-h-[32px]">{{ $features['description'] ?? 'Tailored package for marketplace businesses.' }}</p>
                    <div class="mt-4 flex items-baseline">
                        <span class="text-3xl font-extrabold text-slate-900">{{ number_format($plan->price) }}</span>
                        <span class="text-xs font-semibold text-slate-500 ml-1.5">{{ $plan->currency }} / month</span>
                    </div>
                </div>

                <!-- Feature Bullets -->
                <ul class="space-y-3 text-xs text-slate-700 flex-1 border-t border-slate-100 pt-4 mb-6">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span><strong>{{ ($features['posts_per_day'] ?? 5) < 0 ? 'Unlimited' : ($features['posts_per_day'] ?? 5) }}</strong> Items posted per day</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 {{ !empty($features['customer_phone_access']) ? 'text-emerald-600' : 'text-slate-400' }} flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @if(!empty($features['customer_phone_access']))
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-6V7a4 4 0 00-8 0v4h8z"/>
                            @endif
                        </svg>
                        <span>
                            @if(!empty($features['customer_phone_access']))
                                <strong>Full Direct Customer Phone & WhatsApp</strong>
                            @else
                                <span class="text-slate-500">Masked Customer Phone (Inquiry only)</span>
                            @endif
                        </span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 {{ !empty($features['ai_username_branding']) || !empty($features['ai_customer_assistant']) ? 'text-emerald-600' : 'text-slate-400' }} flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>
                            @if(!empty($features['ai_username_branding']))
                                <strong>Branded AI Assistant based on Dealer's Username</strong> (@{{ $org?->subdomain ?: ($org?->slug ?: 'dealer') }})
                            @elseif(!empty($features['ai_customer_assistant']))
                                <strong>Storefront AI Customer Concierge</strong>
                            @else
                                <span class="text-slate-500">Standard AI Lead Scoring & Descriptions</span>
                            @endif
                        </span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span><strong>Gemini Multimodal Vision Auto-Fill</strong></span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span><strong>{{ $plan->user_limit > 50 ? 'Unlimited' : $plan->user_limit }}</strong> Staff / Sales Agent Accounts</span>
                    </li>
                    @if(!empty($features['export_contacts_csv']))
                    <li class="flex items-center gap-2 text-indigo-700 font-semibold">
                        <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Direct CSV CRM Contact Export</span>
                    </li>
                    @endif
                    @if(!empty($features['priority_marketplace']))
                    <li class="flex items-center gap-2 text-indigo-700 font-semibold">
                        <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Top Priority Placement in Marketplace</span>
                    </li>
                    @endif
                </ul>

                <!-- Action Button with Gateway Checkout Integration -->
                <div>
                    @if($isCurrent)
                        <button disabled class="w-full py-3 bg-slate-100 text-slate-500 rounded-xl font-bold text-xs cursor-default">
                            Current Active Plan
                        </button>
                    @else
                        <a 
                            href="{{ route('checkout.subscription.show', $plan->id) }}" 
                            class="block text-center w-full py-3 {{ $isAdvance ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : ($isPremium ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-slate-900 hover:bg-slate-800 text-white') }} rounded-xl font-bold text-xs shadow transition transform hover:scale-[1.01]"
                        >
                            Pay with Telebirr, Card, PayPal &rarr;
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Supported Ethiopian & Global Payment Gateways -->
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 text-center space-y-3">
        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Accepted Payment Methods for Subscription Upgrades</h4>
        <div class="flex flex-wrap items-center justify-center gap-3 text-xs font-semibold text-slate-600">
            <span class="bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Telebirr SuperApp
            </span>
            <span class="bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span> SantimPay Mobile
            </span>
            <span class="bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span> Chapa (CBE Birr / Awash / Dashen)
            </span>
            <span class="bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Mastercard & Visa
            </span>
            <span class="bg-white border border-slate-200 px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-sky-500"></span> PayPal Express
            </span>
        </div>
        <p class="text-[11px] text-slate-400">All payments are instantly processed and verified server-side with automatic invoice receipt generation.</p>
    </div>
</div>
@endsection
