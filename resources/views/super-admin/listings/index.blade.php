@extends('layouts.admin')

@section('title', 'Global Platform Listings Moderation')

@section('content')
<div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('super-admin.dashboard') }}" class="hover:text-blue-600">Super Admin</a>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-slate-700 font-semibold">Marketplace & Commerce</span>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-blue-600 font-semibold">Global Listings Moderation</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-tags text-blue-600"></i>
                <span>Global Listings & Moderation Hub</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Inspect, approve, reject, feature, and moderate listings published across all marketplace categories.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('marketplace.browse') }}" target="_blank" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center space-x-2">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Public Marketplace</span>
            </a>
        </div>
    </div>

    <!-- Quick Sector Shortcuts -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <a href="{{ route('super-admin.auto.index') }}" class="p-3 bg-white hover:bg-blue-50/50 border border-slate-200 rounded-xl flex items-center space-x-3 transition">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-car"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-900 block">Automotive Sector</span>
                <span class="text-[11px] text-slate-400">Manage vehicles & dealers &rarr;</span>
            </div>
        </a>

        <a href="{{ route('super-admin.property.index') }}" class="p-3 bg-white hover:bg-emerald-50/50 border border-slate-200 rounded-xl flex items-center space-x-3 transition">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-building"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-900 block">Real Estate Sector</span>
                <span class="text-[11px] text-slate-400">Manage properties & agencies &rarr;</span>
            </div>
        </a>

        <a href="{{ route('super-admin.electronics.index') }}" class="p-3 bg-white hover:bg-indigo-50/50 border border-slate-200 rounded-xl flex items-center space-x-3 transition">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                <i class="fa-solid fa-laptop"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-900 block">Electronics Sector</span>
                <span class="text-[11px] text-slate-400">Manage gadgets & tech &rarr;</span>
            </div>
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('super-admin.listings.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by listing title, keyword..." class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <div class="sm:w-48">
                <select name="category_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-40">
                <select name="status" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700">
                    <option value="">All Statuses</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5">
                <i class="fa-solid fa-filter"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['q', 'category_id', 'status']))
                <a href="{{ route('super-admin.listings.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Listings Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3.5">Listing Title & Media</th>
                        <th class="px-4 py-3.5">Category</th>
                        <th class="px-4 py-3.5">Organization / Dealer</th>
                        <th class="px-4 py-3.5">Price</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Approval</th>
                        <th class="px-4 py-3.5">Featured</th>
                        <th class="px-4 py-3.5 text-right">Moderation Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($listings as $l)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center space-x-3">
                                    @php
                                        $primaryImg = $l->primaryMedia ?? $l->media->first();
                                    @endphp
                                    @if($primaryImg)
                                        <img src="{{ asset('storage/' . $primaryImg->file_path) }}" alt="{{ $l->title }}" class="w-12 h-12 rounded-lg object-cover border border-slate-200 flex-shrink-0">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 flex-shrink-0">
                                            <i class="fa-solid fa-image text-lg"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('marketplace.show', $l->slug) }}" target="_blank" class="font-bold text-slate-900 hover:text-blue-600 block text-xs line-clamp-1">
                                            {{ $l->title }}
                                        </a>
                                        <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                                            <span><i class="fa-solid fa-eye text-[9px] mr-0.5"></i> {{ $l->views_count }} views</span>
                                            <span>&bull;</span>
                                            <span>{{ $l->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-slate-800">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 font-bold text-slate-700">
                                    {{ $l->category->name }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-800">{{ $l->organization?->name ?? 'Direct Seller' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $l->user?->name ?? 'Unknown' }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-black text-blue-600">
                                {{ $l->currency }} {{ number_format($l->price, 2) }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $l->status === 'published' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($l->status === 'draft' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $l->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ ($l->approval_status ?? 'approved') === 'approved' ? 'bg-emerald-100 text-emerald-800' : (($l->approval_status ?? '') === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $l->approval_status ?? 'approved' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <form method="POST" action="{{ route('super-admin.listings.toggle-feature', $l->id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs transition {{ $l->featured ? 'text-amber-500 hover:text-amber-600' : 'text-slate-300 hover:text-amber-400' }}" title="{{ $l->featured ? 'Click to unfeature' : 'Click to feature on homepage' }}">
                                        <i class="fa-solid fa-star"></i>
                                        <span class="text-[10px] font-semibold ml-0.5">{{ $l->featured ? 'Featured' : 'Standard' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end space-x-1.5">
                                    <a href="{{ route('marketplace.show', $l->slug) }}" target="_blank" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold" title="Preview Public View">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                    @if(($l->approval_status ?? '') !== 'approved')
                                        <form method="POST" action="{{ route('super-admin.listings.approve', $l->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold" title="Approve & Publish">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if(($l->approval_status ?? '') !== 'rejected')
                                        <form method="POST" action="{{ route('super-admin.listings.reject', $l->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-bold" title="Reject / Send to Draft">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('super-admin.listings.destroy', $l->id) }}" onsubmit="return confirm('Are you sure you want to permanently delete this listing?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold" title="Delete Listing">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-tags text-3xl mb-2 text-slate-300 block"></i>
                                No listings found matching your search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $listings->links() }}
        </div>
    </div>
</div>
@endsection