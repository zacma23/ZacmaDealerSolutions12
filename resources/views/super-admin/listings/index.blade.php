@extends('layouts.admin')

@section('title', 'Global Platform Listings')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Global Inventory & Listings</h2>
        <p class="text-xs text-slate-500">Monitor and inspect listings published across all marketplace businesses</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3">Listing Title</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Organization</th>
                        <th class="px-4 py-3">Price</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Views</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($listings as $l)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3 font-bold text-slate-900">
                                <a href="{{ route('marketplace.show', $l->slug) }}" target="_blank" class="hover:text-blue-600">
                                    {{ $l->title }}
                                </a>
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ $l->category->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $l->organization->name }}</td>
                            <td class="px-4 py-3 font-extrabold text-blue-600">{{ $l->currency }} {{ number_format($l->price, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $l->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $l->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $l->views_count }} views</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('marketplace.show', $l->slug) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-semibold text-xs">
                                    Preview
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">No listings published on the platform.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $listings->links() }}</div>
    </div>
</div>
@endsection
