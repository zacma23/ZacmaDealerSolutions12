@extends('layouts.admin')

@section('title', 'Dynamic Category & Custom Field Engine')

@section('content')
<div class="space-y-8" x-data="{ catModal: false, fieldModal: false, activeCatId: null, activeCatName: '' }">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Dynamic Categories & Schema Engine</h2>
            <p class="text-xs text-slate-500">Configure marketplace categories and dynamic attributes without modifying code</p>
        </div>
        <button @click="catModal = true" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow transition flex items-center space-x-1.5">
            <i class="fa-solid fa-plus"></i>
            <span>Add New Category</span>
        </button>
    </div>

    <!-- Category Cards List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $cat)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                                <i class="{{ $cat->icon ?: 'fa-solid fa-layer-group' }}"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-base text-slate-900">{{ $cat->name }}</h3>
                                <span class="text-[11px] font-mono text-slate-400">slug: {{ $cat->slug }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Fields Pill List -->
                    <div class="mt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-[11px] uppercase font-bold tracking-wider text-slate-400">Dynamic Attributes ({{ $cat->fields->count() }})</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($cat->fields as $f)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $f->label }} <span class="text-slate-400 ml-1">({{ $f->field_type }})</span>
                                </span>
                            @empty
                                <span class="text-xs text-slate-400 italic">No custom fields defined yet.</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Add Field Button -->
                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button @click="activeCatId = {{ $cat->id }}; activeCatName = '{{ $cat->name }}'; fieldModal = true;" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center space-x-1">
                        <i class="fa-solid fa-plus-circle text-xs"></i>
                        <span>Add Dynamic Attribute</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200 text-slate-400">
                No categories created yet. Click "Add New Category" above.
            </div>
        @endforelse
    </div>

    <!-- Create Category Modal -->
    <div x-show="catModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="catModal = false">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-base text-slate-900">Create New Marketplace Category</h3>
                <button @click="catModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('super-admin.categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Category Name</label>
                    <input type="text" name="name" required placeholder="e.g. Heavy Machinery, Agricultural Equipment, Jobs" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">FontAwesome Icon Class</label>
                    <input type="text" name="icon" placeholder="fa-solid fa-tractor" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Description (optional)</label>
                    <textarea name="description" rows="2" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none" placeholder="Description of this category..."></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-xs shadow transition">
                    Create Category
                </button>
            </form>
        </div>
    </div>

    <!-- Create Dynamic Field Modal -->
    <div x-show="fieldModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="fieldModal = false">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-base text-slate-900">Add Field to: <span x-text="activeCatName" class="text-blue-600"></span></h3>
                <button @click="fieldModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form :action="'{{ url('super-admin/categories') }}/' + activeCatId + '/fields'" method="POST" class="space-y-4" x-data="{ ftype: 'text' }">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Display Label</label>
                    <input type="text" name="label" required placeholder="e.g. Operating Hours, Mileage, Transmission, RAM" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Programmatic Key</label>
                        <input type="text" name="name" required placeholder="e.g. operating_hours" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none font-mono">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Field Type</label>
                        <select name="field_type" x-model="ftype" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none bg-white">
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="select">Dropdown Select</option>
                            <option value="multiselect">Multi-Select</option>
                            <option value="date">Date</option>
                            <option value="boolean">Boolean (Yes/No)</option>
                            <option value="textarea">Textarea</option>
                            <option value="currency">Currency Amount</option>
                        </select>
                    </div>
                </div>

                <div x-show="ftype === 'number'">
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Unit of Measurement</label>
                    <input type="text" name="unit" placeholder="e.g. km, sq.m, GB, HP, Hours" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                </div>

                <div x-show="ftype === 'select' || ftype === 'multiselect'">
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Select Options (comma-separated)</label>
                    <textarea name="options" rows="2" placeholder="Automatic, Manual, Semi-Automatic" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none"></textarea>
                </div>

                <div class="flex items-center space-x-4 pt-2 text-xs">
                    <label class="flex items-center space-x-1.5 text-slate-600">
                        <input type="checkbox" name="is_required" value="1" class="rounded text-blue-600">
                        <span>Required</span>
                    </label>
                    <label class="flex items-center space-x-1.5 text-slate-600">
                        <input type="checkbox" name="is_filterable" value="1" checked class="rounded text-blue-600">
                        <span>Faceted Filter</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-xs shadow transition mt-2">
                    Save Dynamic Attribute
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
