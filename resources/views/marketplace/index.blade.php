@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="relative bg-slate-900 text-white overflow-hidden py-20 lg:py-24">
    <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#3b82f6_1px,transparent_1px)] [background-size:16px_16px]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto space-y-6">
            <span class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-400/30">
                <i class="fa-solid fa-sparkles"></i>
                <span>AI-Powered Marketplace + CRM SaaS</span>
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight">
                Buy, Sell & Connect Across <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300">Any Industry</span>
            </h1>
            <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                Explore premium verified listings across vehicles, real estate, electronics, machinery, and services. Powered by multi-tenant business CRM.
            </p>

            <!-- Search Form -->
            <form action="{{ route('marketplace.browse') }}" method="GET" class="bg-white p-2 rounded-2xl shadow-xl flex flex-col sm:flex-row gap-2 max-w-2xl mx-auto text-slate-800">
                <div class="flex-1 flex items-center px-3 space-x-2">
                    <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                    <input type="text" name="q" placeholder="Search cars, apartments, laptops..." class="w-full text-sm outline-none bg-transparent">
                </div>
                <div class="sm:w-44 border-t sm:border-t-0 sm:border-l border-slate-200 flex items-center px-3">
                    <i class="fa-solid fa-location-dot text-slate-400 mr-2"></i>
                    <input type="text" name="city" placeholder="City / Location" class="w-full text-sm outline-none bg-transparent">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-3 rounded-xl transition shadow">
                    Search
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Categories Grid -->
<section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Browse Categories</h2>
            <p class="text-sm text-slate-500">Universal dynamic listing categories with custom attribute filtering</p>
        </div>
        <a href="{{ route('marketplace.browse') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
            View All <i class="fa-solid fa-arrow-right text-xs ml-1"></i>
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($categories as $cat)
            <a href="{{ route('marketplace.category', $cat->slug) }}" class="bg-white border border-slate-200 rounded-xl p-5 text-center hover:shadow-md hover:border-blue-400 transition group flex flex-col items-center justify-center space-y-3">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl group-hover:bg-blue-600 group-hover:text-white transition">
                    <i class="{{ $cat->icon ?: 'fa-solid fa-layer-group' }}"></i>
                </div>
                <span class="text-sm font-semibold text-slate-800 group-hover:text-blue-600">{{ $cat->name }}</span>
            </a>
        @endforeach
    </div>
</section>

<!-- Featured Listings -->
@if($featured->isNotEmpty())
<section class="py-12 bg-slate-100/70 border-y border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-2 mb-6">
            <span class="text-amber-500 text-lg"><i class="fa-solid fa-star"></i></span>
            <h2 class="text-2xl font-bold text-slate-900">Featured Opportunities</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featured as $item)
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-lg transition flex flex-col">
                    <div class="relative h-48 bg-slate-200 overflow-hidden">
                        <img src="{{ $item->getPrimaryImageUrl() }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                        <span class="absolute top-3 left-3 bg-amber-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow">
                            Featured
                        </span>
                        <span class="absolute bottom-3 right-3 bg-slate-900/80 backdrop-blur-sm text-white text-xs font-semibold px-2 py-0.5 rounded">
                            {{ $item->category->name }}
                        </span>
                    </div>

                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="text-xs text-slate-500 mb-1 flex items-center space-x-1">
                                <i class="fa-solid fa-building text-[10px]"></i>
                                <span>{{ $item->organization->name }}</span>
                                <span>•</span>
                                <span>{{ $item->city ?? 'Ethiopia' }}</span>
                            </div>
                            <h3 class="font-bold text-base text-slate-900 line-clamp-1 mb-2">
                                <a href="{{ route('marketplace.show', $item->slug) }}" class="hover:text-blue-600">
                                    {{ $item->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-slate-500 line-clamp-2 mb-4">
                                {{ Str::limit(strip_tags($item->description), 100) }}
                            </p>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-xs text-slate-400 block">{{ ucfirst($item->price_type) }}</span>
                                <span class="text-lg font-extrabold text-blue-600">
                                    {{ $item->currency }} {{ number_format($item->price, 2) }}
                                </span>
                            </div>
                            <a href="{{ route('marketplace.show', $item->slug) }}" class="px-3.5 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-blue-600 rounded-lg transition">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Latest Listings -->
<section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Latest Additions</h2>
            <p class="text-sm text-slate-500">Freshly listed inventory from authorized dealers and verified sellers</p>
        </div>
        <a href="{{ route('marketplace.browse') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
            Browse All <i class="fa-solid fa-arrow-right text-xs ml-1"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($latest as $item)
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                <div class="relative h-44 bg-slate-200 overflow-hidden">
                    <img src="{{ $item->getPrimaryImageUrl() }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                    <span class="absolute bottom-2 right-2 bg-slate-900/80 backdrop-blur-sm text-white text-[10px] font-semibold px-2 py-0.5 rounded">
                        {{ $item->category->name }}
                    </span>
                </div>

                <div class="p-4 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] text-slate-400 mb-1 flex items-center justify-between">
                            <span>{{ $item->city ?? 'Addis Ababa' }}</span>
                            <span>{{ $item->created_at->diffForHumans() }}</span>
                        </div>
                        <h3 class="font-semibold text-sm text-slate-900 line-clamp-1 mb-2">
                            <a href="{{ route('marketplace.show', $item->slug) }}" class="hover:text-blue-600">
                                {{ $item->title }}
                            </a>
                        </h3>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-base font-bold text-slate-900">
                            {{ $item->currency }} {{ number_format($item->price) }}
                        </span>
                        <a href="{{ route('marketplace.show', $item->slug) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                            View <i class="fa-solid fa-chevron-right text-[10px] ml-0.5"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-500">
                <p>No listings published yet. Check back soon!</p>
            </div>
        @endforelse
    </div>
</section>

<!-- SaaS Promotion Banner -->
<section class="py-16 bg-gradient-to-r from-blue-700 to-indigo-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl space-y-6">
        <h2 class="text-3xl font-extrabold tracking-tight">
            Run Your Dealership or Marketplace with Zacma SaaS
        </h2>
        <p class="text-blue-100 text-base leading-relaxed">
            Get an isolated multi-tenant portal with built-in CRM, sales pipeline, dynamic inventory engine, AI lead scoring, and automated payment gateway integrations.
        </p>
        <div class="pt-2">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-6 py-3.5 text-sm font-bold text-blue-700 bg-white hover:bg-blue-50 rounded-xl shadow-lg transition">
                Start Your Business Portal
            </a>
        </div>
    </div>
</section>
@endsection
