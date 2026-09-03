@extends('layouts.admin')

@section('title', 'SaaS Plans & Subscription Quotas')

@section('content')
<div class="space-y-6" x-data="{ planModal: false }">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">SaaS Subscription Plans & Feature Limits</h2>
            <p class="text-xs text-slate-500">Configure monetization tiers and feature limits for businesses</p>
        </div>
        <button @click="planModal = true" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow transition flex items-center space-x-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>Add Plan Tier</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($plans as $p)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h3 class="font-bold text-base text-slate-900">{{ $p->name }}</h3>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-blue-50 text-blue-700">{{ $p->organizations_count }} orgs</span>
                    </div>

                    <div class="my-4">
                        <span class="text-3xl font-extrabold text-slate-900">{{ $p->currency }} {{ number_format($p->price, 0) }}</span>
                        <span class="text-xs text-slate-400">/ {{ $p->interval }}</span>
                    </div>

                    <ul class="space-y-2 text-xs text-slate-600 pt-2 border-t border-slate-100">
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-emerald-500 text-xs"></i>
                            <span><strong>{{ $p->listing_limit }}</strong> Listings</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-emerald-500 text-xs"></i>
                            <span><strong>{{ $p->contact_limit }}</strong> CRM Contacts</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-emerald-500 text-xs"></i>
                            <span><strong>{{ $p->user_limit }}</strong> Team Users</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-check text-emerald-500 text-xs"></i>
                            <span><strong>{{ $p->ai_request_limit }}</strong> AI Operations / mo</span>
                        </li>
                    </ul>
                </div>

                <div class="pt-4 border-t border-slate-100 text-center">
                    <span class="text-[10px] text-slate-400">Slug: {{ $p->slug }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200 text-slate-400">
                No plans created yet.
            </div>
        @endforelse
    </div>

    <!-- Create Plan Modal -->
    <div x-show="planModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="planModal = false">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-base text-slate-900">Create Subscription Plan Tier</h3>
                <button @click="planModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('super-admin.plans.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Plan Name</label>
                    <input type="text" name="name" required placeholder="e.g. Starter, Pro, Business, Enterprise" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Price</label>
                        <input type="number" step="0.01" name="price" required placeholder="0.00" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Currency</label>
                        <select name="currency" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none bg-white">
                            <option value="USD">USD</option>
                            <option value="ETB">ETB</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Max Listings</label>
                        <input type="number" name="listing_limit" value="500" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Max CRM Contacts</label>
                        <input type="number" name="contact_limit" value="1000" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Max Users</label>
                        <input type="number" name="user_limit" value="5" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">AI Limit / Mo</label>
                        <input type="number" name="ai_request_limit" value="200" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                    </div>
                </div>

                <input type="hidden" name="interval" value="monthly">

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-xs shadow transition">
                    Save Plan Tier
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
