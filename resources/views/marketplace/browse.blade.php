@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header & Search Bar -->
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mb-2">
            {{ $category ? $category->name : 'All Marketplace Listings' }}
        </h1>
        <p class="text-xs text-slate-500 mb-6">
            Showing {{ $listings->total() }} available listings
        </p>

        <!-- Category Tabs -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-2 border-b border-slate-200">
            <a href="{{ route('marketplace.browse') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ !$category ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                All Categories
            </a>
            @foreach($categories as $c)
                <a href="{{ route('marketplace.category', $c->slug) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ $category && $category->id === $c->id ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                    <i class="{{ $c->icon ?: 'fa-solid fa-tag' }} mr-1"></i> {{ $c->name }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Filters -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm h-fit space-y-6">
            <form action="{{ $category ? route('marketplace.category', $category->slug) : route('marketplace.browse') }}" method="GET" class="space-y-5">
                <div>
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1">Keywords</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search title or details..." class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 outline-none focus:border-blue-600">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1">City / Location</label>
                    <input type="text" name="city" value="{{ request('city') }}" placeholder="e.g. Addis Ababa" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 outline-none focus:border-blue-600">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1">Price Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 outline-none">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 outline-none">
                    </div>
                </div>

                <!-- Dynamic Category Attributes Filtering -->
                @if($category && $category->fields->where('is_filterable', true)->isNotEmpty())
                    <div class="pt-4 border-t border-slate-100 space-y-4">
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-wider block">
                            {{ $category->name }} Attributes
                        </span>

                        @foreach($category->fields->where('is_filterable', true) as $f)
                            <div>
                                <label class="text-xs font-semibold text-slate-700 block mb-1">{{ $f->label }}</label>
                                @if(in_array($f->field_type, ['select', 'multiselect']) && $f->options->isNotEmpty())
                                    <select name="fields[{{ $f->name }}]" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 outline-none bg-white">
                                        <option value="">Any {{ $f->label }}</option>
                                        @foreach($f->options as $opt)
                                            <option value="{{ $opt->value }}" {{ request("fields.{$f->name}") == $opt->value ? 'selected' : '' }}>
                                                {{ $opt->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                @elseif($f->field_type === 'number')
                                    <input type="number" name="fields[{{ $f->name }}]" value="{{ request("fields.{$f->name}") }}" placeholder="Filter by {{ strtolower($f->label) }}" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 outline-none">
                                @else
                                    <input type="text" name="fields[{{ $f->name }}]" value="{{ request("fields.{$f->name}") }}" placeholder="Any..." class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 outline-none">
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="pt-4 border-t border-slate-100 flex items-center space-x-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs py-2.5 rounded-lg shadow-sm">
                        Apply Filters
                    </button>
                    <a href="{{ $category ? route('marketplace.category', $category->slug) : route('marketplace.browse') }}" class="px-3 py-2.5 text-xs text-slate-500 hover:text-slate-800 border border-slate-200 rounded-lg">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Listings Grid -->
        <div class="lg:col-span-3 space-y-6">
            <div class="flex justify-between items-center bg-white p-3.5 rounded-xl border border-slate-200 text-xs text-slate-600">
                <span>Showing <strong class="text-slate-900">{{ $listings->firstItem() ?? 0 }}-{{ $listings->lastItem() ?? 0 }}</strong> of {{ $listings->total() }} listings</span>
                <div class="flex items-center space-x-2">
                    <label>Sort by:</label>
                    <select onchange="location = this.value;" class="border border-slate-300 rounded-md px-2 py-1 outline-none bg-white">
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest First</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Viewed</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($listings as $item)
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                        <div class="relative h-44 bg-slate-200 overflow-hidden">
                            <img src="{{ $item->getPrimaryImageUrl() }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                            <span class="absolute bottom-2 right-2 bg-slate-900/80 backdrop-blur-sm text-white text-[10px] font-semibold px-2 py-0.5 rounded">
                                {{ $item->category->name }}
                            </span>
                            @if($item->featured)
                                <span class="absolute top-2 left-2 bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow">
                                    FEATURED
                                </span>
                            @endif
                        </div>

                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] text-slate-400 mb-1 flex items-center justify-between">
                                    <span>{{ $item->city ?? 'Addis Ababa' }}</span>
                                    <span>{{ $item->organization->name }}</span>
                                </div>
                                <h3 class="font-bold text-sm text-slate-900 line-clamp-1 mb-2">
                                    <a href="{{ route('marketplace.show', $item->slug) }}" class="hover:text-blue-600">
                                        {{ $item->title }}
                                    </a>
                                </h3>

                                <!-- Show 2 dynamic fields in card if configured -->
                                <div class="flex flex-wrap gap-1 mb-3">
                                    @foreach($item->fieldValues->take(2) as $fv)
                                        @if($fv->value)
                                            <span class="bg-slate-100 text-slate-600 text-[10px] font-medium px-2 py-0.5 rounded">
                                                {{ $fv->categoryField->label }}: {{ $fv->value }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-base font-extrabold text-blue-600">
                                    {{ $item->currency }} {{ number_format($item->price, 2) }}
                                </span>
                                <a href="{{ route('marketplace.show', $item->slug) }}" class="px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-blue-600 rounded-lg transition">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-slate-200">
                        <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-3"></i>
                        <h3 class="text-base font-bold text-slate-800">No listings found</h3>
                        <p class="text-xs text-slate-500 mt-1">Try adjusting your filters, location, or search keywords.</p>
                    </div>
                @endforelse
            </div>

            <div class="pt-4">
                {{ $listings->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
