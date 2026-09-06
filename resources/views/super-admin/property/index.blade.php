@extends('layouts.admin')

@section('title', 'Real Estate & Property Command')

@section('content')
<div class="space-y-6">
    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('super-admin.dashboard') }}" class="hover:text-blue-600">Super Admin</a>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-slate-700 font-semibold">Industry Sectors</span>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
                <span class="text-blue-600 font-semibold">Real Estate & Property</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-building text-emerald-600"></i>
                <span>Real Estate Sector Control</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Centralized properties, commercial estates, land, residential listings, and agency oversight.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('marketplace.category', 'property') }}" target="_blank" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center space-x-2">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Public Property Market</span>
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Properties</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">{{ number_format($totalProperties) }}</span>
                <span class="text-[11px] text-slate-400">All agencies & developers</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-city"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Published & Active</span>
                <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ number_format($activeProperties) }}</span>
                <span class="text-[11px] text-emerald-600 font-semibold">Live on marketplace</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Sector Category</span>
                <span class="text-base font-black text-slate-900 mt-1 block">{{ $category?->name ?? 'Property' }}</span>
                <a href="{{ route('super-admin.categories.index') }}" class="text-[11px] text-blue-600 hover:underline font-semibold">Edit Dynamic Fields &rarr;</a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-sliders"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('super-admin.property.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by property title, location, type..." class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <div class="sm:w-48">
                <select name="status" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700">
                    <option value="">All Statuses</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5">
                <i class="fa-solid fa-filter"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['q', 'status']))
                <a href="{{ route('super-admin.property.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Properties Inventory Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3.5">Property Details</th>
                        <th class="px-4 py-3.5">Agency / Broker</th>
                        <th class="px-4 py-3.5">Price</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Approval</th>
                        <th class="px-4 py-3.5">Featured</th>
                        <th class="px-4 py-3.5 text-right">Moderation Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($listings as $p)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center space-x-3">
                                    @php
                                        $primaryImg = $p->media->where('is_primary', true)->first() ?? $p->media->first();
                                    @endphp
                                    @if($primaryImg)
                                        <img src="{{ asset('storage/' . $primaryImg->file_path) }}" alt="{{ $p->title }}" class="w-12 h-12 rounded-lg object-cover border border-slate-200 flex-shrink-0">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 flex-shrink-0">
                                            <i class="fa-solid fa-house text-lg"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('marketplace.show', $p->slug) }}" target="_blank" class="font-bold text-slate-900 hover:text-blue-600 block text-xs line-clamp-1">
                                            {{ $p->title }}
                                        </a>
                                        <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                                            <span><i class="fa-solid fa-eye text-[9px] mr-0.5"></i> {{ $p->views_count }} views</span>
                                            <span>&bull;</span>
                                            <span>Added {{ $p->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-800">{{ $p->organization?->name ?? 'Individual Owner' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $p->user?->name ?? 'Unknown' }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-black text-emerald-600">
                                {{ $p->currency }} {{ number_format($p->price, 2) }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $p->status === 'published' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($p->status === 'draft' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ ($p->approval_status ?? 'approved') === 'approved' ? 'bg-emerald-100 text-emerald-800' : (($p->approval_status ?? '') === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $p->approval_status ?? 'approved' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <form method="POST" action="{{ route('super-admin.listings.toggle-feature', $p->id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs transition {{ $p->featured ? 'text-amber-500 hover:text-amber-600' : 'text-slate-300 hover:text-amber-400' }}" title="{{ $p->featured ? 'Click to unfeature' : 'Click to feature on homepage' }}">
                                        <i class="fa-solid fa-star"></i>
                                        <span class="text-[10px] font-semibold ml-0.5">{{ $p->featured ? 'Featured' : 'Standard' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end space-x-1.5">
                                    <a href="{{ route('marketplace.show', $p->slug) }}" target="_blank" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold" title="Preview Public View">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                    @if(($p->approval_status ?? '') !== 'approved')
                                        <form method="POST" action="{{ route('super-admin.listings.approve', $p->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold" title="Approve & Publish">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if(($p->approval_status ?? '') !== 'rejected')
                                        <form method="POST" action="{{ route('super-admin.listings.reject', $p->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-bold" title="Reject / Send to Draft">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('super-admin.listings.destroy', $p->id) }}" onsubmit="return confirm('Are you sure you want to permanently delete this listing?');" class="inline">
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
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-building text-3xl mb-2 text-slate-300 block"></i>
                                No property listings found matching your search.
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