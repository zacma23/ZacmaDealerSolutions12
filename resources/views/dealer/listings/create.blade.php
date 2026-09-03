@extends('layouts.admin')

@section('title', 'Add New Listing (AI-Enhanced)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="listingCreator()">
    <div class="flex justify-between items-center">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Create Universal Listing</h2>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-2.5 py-0.5 rounded-full shadow-sm">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    AI-Enhanced
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Upload a photo or enter hints to have Gemini AI auto-detect specifications, estimate pricing, and fill the form.</p>
        </div>
        <a href="{{ route('dealer.listings.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; Back to Inventory
        </a>
    </div>

    @if(isset($org))
        <!-- Quota & Subscription Status Bar -->
        <div class="bg-white rounded-xl border border-slate-200 p-3.5 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-700">Daily Quota:</span>
                <span class="bg-slate-100 text-slate-800 font-semibold px-2 py-0.5 rounded">
                    {{ $postsTodayCount }} / {{ $dailyPostsAllowed < 0 ? 'Unlimited' : $dailyPostsAllowed }} posted today
                </span>
                <span class="text-slate-400">({{ $org->plan?->name ?? 'Basic' }} Plan)</span>
            </div>
            <div>
                <a href="{{ route('dealer.subscription.index') }}" class="text-blue-600 font-bold hover:underline inline-flex items-center gap-1">
                    <span>Manage Plans & Limits</span> &rarr;
                </a>
            </div>
        </div>

        @if(!$canPostToday)
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-xs text-amber-900">
                    <strong class="font-bold">Daily Posting Limit Reached:</strong> You have reached your limit of {{ $dailyPostsAllowed }} listings today on the {{ $org->plan?->name }} tier.
                    <a href="{{ route('dealer.subscription.index') }}" class="font-bold underline text-amber-900 hover:text-amber-950 ml-1">
                        Upgrade to Premium (20/day) or Advance (Unlimited) to post immediately &rarr;
                    </a>
                </div>
            </div>
        @endif
    @endif

    <!-- AI MAGIC AUTO-DETECTION HERO CARD -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 rounded-2xl border border-emerald-500/30 p-6 shadow-xl text-white relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-start justify-between gap-4 mb-4 relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>Gemini Multimodal Auto-Fill</span>
                        <span class="text-[10px] bg-emerald-400/20 text-emerald-300 px-2 py-0.5 rounded font-mono">Vision + Text</span>
                    </h3>
                    <p class="text-xs text-slate-300">Upload a photo of any vehicle, real estate, electronic item, or machinery to auto-detect all details.</p>
                </div>
            </div>

            <template x-if="aiDetectedBadge">
                <span class="inline-flex items-center gap-1.5 text-xs bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 px-3 py-1 rounded-full font-medium animate-pulse">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Auto-Filled with AI
                </span>
            </template>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end relative z-10">
            <!-- Photo Upload Area for AI -->
            <div class="md:col-span-4">
                <label class="block text-[11px] font-semibold text-slate-300 mb-1.5">Product / Item Photo</label>
                <div class="relative border-2 border-dashed border-slate-600 hover:border-emerald-400 rounded-xl p-3 bg-slate-950/40 text-center transition cursor-pointer flex items-center justify-center min-h-[90px]">
                    <input type="file" @change="onAiFileChange($event)" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                    <template x-if="!imagePreviewUrl">
                        <div class="space-y-1">
                            <svg class="w-6 h-6 mx-auto text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-[11px] text-slate-300 font-medium">Click or drop photo here</p>
                            <p class="text-[10px] text-slate-500">JPG, PNG, WebP up to 10MB</p>
                        </div>
                    </template>
                    <template x-if="imagePreviewUrl">
                        <div class="flex items-center gap-3 w-full text-left">
                            <img :src="imagePreviewUrl" class="w-14 h-14 object-cover rounded-lg border border-slate-700">
                            <div class="overflow-hidden flex-1">
                                <p class="text-[11px] font-medium text-emerald-300 truncate" x-text="imageFileName"></p>
                                <p class="text-[10px] text-slate-400">Photo ready for scan</p>
                                <span class="text-[10px] text-slate-400 hover:text-rose-400 cursor-pointer" @click.stop="clearAiImage()">Remove</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Optional Hints -->
            <div class="md:col-span-5">
                <label class="block text-[11px] font-semibold text-slate-300 mb-1.5">Optional Keywords or Quick Hint</label>
                <input 
                    type="text" 
                    x-model="aiHints" 
                    placeholder="e.g. 2023 Toyota RAV4 Hybrid XLE, pearl white, low mileage"
                    class="w-full text-xs bg-slate-950/60 border border-slate-700 rounded-xl px-3.5 py-3 text-white placeholder-slate-500 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition"
                >
                <p class="text-[10px] text-slate-400 mt-1">Leave blank to let AI detect purely from photo, or add custom context.</p>
            </div>

            <!-- Scan Button -->
            <div class="md:col-span-3">
                <button 
                    type="button" 
                    @click="detectWithAi()" 
                    :disabled="aiLoading || (!aiImage && !aiHints.trim())"
                    class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 disabled:opacity-40 text-white font-bold py-3 px-4 rounded-xl text-xs shadow-lg transition transform hover:scale-[1.02]"
                >
                    <template x-if="!aiLoading">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Scan & Auto-Fill</span>
                        </span>
                    </template>
                    <template x-if="aiLoading">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Gemini Analyzing...</span>
                        </span>
                    </template>
                </button>
            </div>
        </div>

        <!-- AI Detected Highlights Banner -->
        <template x-if="aiFeatures && aiFeatures.length > 0">
            <div class="mt-4 pt-3 border-t border-slate-800 flex flex-wrap items-center gap-2 text-xs">
                <span class="text-[11px] text-emerald-400 font-semibold uppercase tracking-wider">Detected Features:</span>
                <template x-for="(feat, idx) in aiFeatures" :key="idx">
                    <span class="bg-emerald-950/80 border border-emerald-500/30 text-emerald-300 px-2.5 py-0.5 rounded-full text-[11px]" x-text="feat"></span>
                </template>
            </div>
        </template>
    </div>

    <!-- MAIN FORM -->
    <form action="{{ route('dealer.listings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 1. Category & Title -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Basic Information</h3>
                <span class="text-[11px] text-slate-400 font-medium">Step 1 of 3</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Target Category</label>
                    <select name="category_id" x-model="selectedCatId" required class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none bg-white focus:border-emerald-600">
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Listing Title</label>
                    <input type="text" name="title" x-model="title" required placeholder="e.g. 2024 Toyota RAV4 Hybrid Limited" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none focus:border-emerald-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Price</label>
                    <input type="number" step="0.01" name="price" x-model="price" required placeholder="2500000" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none focus:border-emerald-600">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Currency</label>
                    <select name="currency" x-model="currency" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none bg-white">
                        <option value="ETB">ETB (Ethiopian Birr)</option>
                        <option value="USD">USD (US Dollar)</option>
                        <option value="EUR">EUR</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Price Type</label>
                    <select name="price_type" x-model="priceType" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none bg-white">
                        <option value="fixed">Fixed</option>
                        <option value="negotiable">Negotiable</option>
                        <option value="call_for_price">Call for Price</option>
                        <option value="hourly">Hourly</option>
                        <option value="daily">Daily</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Description</label>
                <textarea name="description" x-model="description" rows="4" placeholder="Detailed specifications, history, condition..." class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none focus:border-emerald-600 leading-relaxed"></textarea>
            </div>
        </div>

        <!-- 2. Dynamic Category-Specific Attributes -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    2. <span x-text="selectedCategory?.name"></span> Specifications
                </h3>
                <span class="text-[11px] text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded">Dynamic Schema Active</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <template x-for="field in selectedCategory?.fields" :key="field.id">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">
                            <span x-text="field.name"></span>
                            <span x-show="field.unit" class="text-slate-400 text-[10px]" x-text="'(' + field.unit + ')'"></span>
                            <span x-show="field.is_required" class="text-rose-500">*</span>
                        </label>

                        <!-- Dropdown Select -->
                        <template x-if="field.field_type === 'select' || field.field_type === 'multiselect'">
                            <select :name="'fields[' + field.id + ']'" x-model="fieldValues[field.id]" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none bg-white">
                                <option value="">Select option...</option>
                                <template x-for="opt in field.options" :key="opt.id">
                                    <option :value="opt.value" x-text="opt.label"></option>
                                </template>
                            </select>
                        </template>

                        <!-- Number Input -->
                        <template x-if="field.field_type === 'number'">
                            <input type="number" :name="'fields[' + field.id + ']'" x-model="fieldValues[field.id]" placeholder="Enter number..." class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none">
                        </template>

                        <!-- Text Input -->
                        <template x-if="field.field_type === 'text'">
                            <input type="text" :name="'fields[' + field.id + ']'" x-model="fieldValues[field.id]" placeholder="Enter value..." class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none">
                        </template>

                        <!-- Date Input -->
                        <template x-if="field.field_type === 'date'">
                            <input type="date" :name="'fields[' + field.id + ']'" x-model="fieldValues[field.id]" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none">
                        </template>

                        <!-- Boolean (Yes/No) -->
                        <template x-if="field.field_type === 'boolean'">
                            <select :name="'fields[' + field.id + ']'" x-model="fieldValues[field.id]" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none bg-white">
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- 3. Location & Media -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100">3. Location & Photos</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">City / Region</label>
                    <input type="text" name="city" x-model="city" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Physical Address / Showroom</label>
                    <input type="text" name="address" x-model="address" placeholder="Bole Sub-City, Road 22" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none">
                </div>
            </div>

            <!-- Upload Photos -->
            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Upload Listing Photos</label>
                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-300 rounded-xl p-2">
            </div>
        </div>

        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl text-sm shadow-md transition">
            Publish Universal Listing
        </button>
    </form>
</div>

<script>
function listingCreator() {
    return {
        categories: {{ Js::from($categories) }},
        selectedCatId: '{{ $categories->first()?->id }}',
        title: '',
        price: '',
        currency: 'ETB',
        priceType: 'fixed',
        city: 'Addis Ababa',
        address: '',
        description: '',
        fieldValues: {},
        aiImage: null,
        imageFileName: '',
        imagePreviewUrl: null,
        aiHints: '',
        aiLoading: false,
        aiDetectedBadge: false,
        aiFeatures: [],

        get selectedCategory() {
            return this.categories.find(c => c.id == this.selectedCatId);
        },

        onAiFileChange(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.aiImage = file;
            this.imageFileName = file.name;
            this.imagePreviewUrl = URL.createObjectURL(file);
        },

        clearAiImage() {
            this.aiImage = null;
            this.imageFileName = '';
            this.imagePreviewUrl = null;
        },

        detectWithAi() {
            this.aiLoading = true;
            const formData = new FormData();
            if (this.aiImage) {
                formData.append('image', this.aiImage);
            }
            if (this.aiHints.trim()) {
                formData.append('hints', this.aiHints.trim());
            }
            if (this.selectedCatId) {
                formData.append('category_id', this.selectedCatId);
            }

            fetch('{{ route('dealer.listings.ai-detect') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                this.aiLoading = false;
                if (res.success && res.data) {
                    const d = res.data;
                    if (d.title) this.title = d.title;
                    if (d.suggested_price) this.price = d.suggested_price;
                    if (d.currency) this.currency = d.currency;
                    if (d.price_type) this.priceType = d.price_type;
                    if (d.city) this.city = d.city;
                    if (d.address) this.address = d.address;
                    if (d.description) this.description = d.description;
                    if (d.category_id) this.selectedCatId = d.category_id.toString();

                    if (d.mapped_field_values) {
                        for (const [fId, fVal] of Object.entries(d.mapped_field_values)) {
                            this.fieldValues[fId] = fVal;
                        }
                    }

                    if (d.features) {
                        this.aiFeatures = d.features;
                    }

                    this.aiDetectedBadge = true;
                }
            })
            .catch(err => {
                this.aiLoading = false;
                alert('AI auto-detection error. Please try again.');
            });
        }
    };
}
</script>
@endsection
