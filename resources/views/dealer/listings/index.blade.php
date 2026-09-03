@extends('layouts.admin')

@section('title', 'Dealership Inventory & Listings')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Inventory & Listings</h2>
            <p class="text-xs text-slate-500">Manage vehicles, properties, products, and commercial offerings</p>
        </div>
        <a href="{{ route('dealer.listings.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow transition flex items-center space-x-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>Add New Listing</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3">Listing Item</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Price</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Inquiries</th>
                        <th class="px-4 py-3">Views</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($listings as $l)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-3">
                                    <img src="{{ $l->getPrimaryImageUrl() }}" class="w-12 h-9 rounded-lg object-cover border border-slate-200">
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm line-clamp-1">{{ $l->title }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $l->city ?? 'Addis Ababa' }} • {{ $l->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-700">{{ $l->category->name }}</td>
                            <td class="px-4 py-3 font-extrabold text-blue-600">{{ $l->currency }} {{ number_format($l->price, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $l->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $l->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $l->inquiries_count }} leads</td>
                            <td class="px-4 py-3 text-slate-500">{{ $l->views_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('marketplace.show', $l->slug) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-semibold transition">
                                    View on Marketplace &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">No inventory items added yet. Click "Add New Listing" to publish one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $listings->links() }}</div>
    </div>
</div>
@endsection
