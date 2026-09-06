@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ inquiryOpen: false, bookOpen: false }">
    <!-- Breadcrumb -->
    <nav class="flex text-xs text-slate-500 mb-6 space-x-2">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
        <span>/</span>
        <a href="{{ route('marketplace.category', $listing->category->slug) }}" class="hover:text-blue-600">{{ $listing->category->name }}</a>
        <span>/</span>
        <span class="text-slate-800 font-semibold truncate">{{ $listing->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Media Gallery & Details -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Gallery -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm" x-data="{ activeImg: '{{ $listing->getPrimaryImageUrl() }}' }">
                <div class="h-96 sm:h-[420px] bg-slate-100 flex items-center justify-center relative">
                    <img :src="activeImg" alt="{{ $listing->title }}" class="w-full h-full object-contain">
                    @if($listing->featured)
                        <span class="absolute top-4 left-4 bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow">
                            Featured
                        </span>
                    @endif
                </div>

                @if($listing->media->count() > 1)
                    <div class="p-4 flex space-x-3 overflow-x-auto border-t border-slate-100 bg-slate-50">
                        @foreach($listing->media as $m)
                            <button @click="activeImg = '{{ $m->getUrl() }}'" class="w-20 h-14 rounded-lg overflow-hidden border-2 focus:border-blue-600 flex-shrink-0 bg-white">
                                <img src="{{ $m->getUrl() }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Dynamic Specifications Table -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center space-x-2">
                    <i class="fa-solid fa-list-check text-blue-600"></i>
                    <span>{{ $listing->category->name }} Specifications</span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($listing->fieldValues as $val)
                        <div class="flex justify-between py-2.5 px-3 rounded-lg bg-slate-50 border border-slate-100 text-xs">
                            <span class="font-medium text-slate-500">{{ $val->categoryField->label }}</span>
                            <span class="font-bold text-slate-900">
                                {{ $val->value }} {{ $val->categoryField->unit }}
                            </span>
                        </div>
                    @empty
                        <div class="col-span-full text-xs text-slate-400">No custom attributes listed.</div>
                    @endforelse
                </div>
            </div>

            <!-- Description -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <h2 class="text-lg font-bold text-slate-900">Overview & Description</h2>
                <div class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                    {{ $listing->description ?: 'No detailed description provided.' }}
                </div>
            </div>

            <!-- Location -->
            @if($listing->address || $listing->city)
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900 mb-2">Location</h2>
                    <p class="text-sm text-slate-600 flex items-center space-x-2">
                        <i class="fa-solid fa-location-dot text-rose-500"></i>
                        <span>{{ $listing->address }}, {{ $listing->city }}, {{ $listing->country }}</span>
                    </p>
                </div>
            @endif
        </div>

        <!-- Right Side: Pricing & Actions -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6 sticky top-24">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Price</span>
                    <div class="text-3xl font-extrabold text-blue-600 mt-1">
                        {{ $listing->currency }} {{ number_format($listing->price, 2) }}
                    </div>
                    <span class="text-xs text-slate-500 mt-0.5 block">Price Type: <strong>{{ ucfirst($listing->price_type) }}</strong></span>
                </div>

                <!-- Primary Action Buttons -->
                <div class="space-y-2.5 pt-2">
                    <button @click="inquiryOpen = true" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl text-sm shadow transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-envelope"></i>
                        <span>Contact Seller / Inquire</span>
                    </button>

                    <button @click="bookOpen = true" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-4 rounded-xl text-sm shadow transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Book Viewing / Test Drive</span>
                    </button>

                    <!-- Purchase / Reserve Deposit Button -->
                    <a href="{{ route('checkout.show', ['listing' => $listing->id, 'type' => in_array($listing->category->slug, ['vehicles', 'property']) ? 'vehicle_deposit' : 'product']) }}" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl text-sm shadow transition flex items-center justify-center space-x-2 block text-center">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>{{ in_array($listing->category->slug, ['vehicles', 'property']) ? 'Pay Deposit & Reserve' : 'Buy Now with Verified Checkout' }}</span>
                    </a>
                </div>

                <!-- Dealer / Seller Info Card -->
                <div class="pt-6 border-t border-slate-100 space-y-3">
                    <div class="text-xs uppercase tracking-wider text-slate-400 font-bold">Authorized Merchant</div>
                    <div class="flex items-center space-x-3">
                        @if($listing->organization->logo)
                            <img src="{{ asset('storage/' . $listing->organization->logo) }}" class="w-12 h-12 rounded-xl object-contain border border-slate-200">
                        @else
                            <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-lg">
                                {{ strtoupper(substr($listing->organization->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h4 class="font-bold text-sm text-slate-900">{{ $listing->organization->name }}</h4>
                            <div class="text-xs text-emerald-600 font-semibold flex items-center space-x-1">
                                <i class="fa-solid fa-circle-check text-[10px]"></i>
                                <span>Verified Dealer</span>
                            </div>
                        </div>
                    </div>

                    @if($listing->contact_phone)
                        <div class="text-xs text-slate-600 flex items-center space-x-2 pt-2">
                            <i class="fa-solid fa-phone text-slate-400 w-4"></i>
                            <span>{{ $listing->contact_phone }}</span>
                        </div>
                    @endif
                    @if($listing->contact_email)
                        <div class="text-xs text-slate-600 flex items-center space-x-2">
                            <i class="fa-solid fa-envelope text-slate-400 w-4"></i>
                            <span>{{ $listing->contact_email }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Inquiry Modal -->
    <div x-show="inquiryOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="inquiryOpen = false">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-lg text-slate-900">Inquire About This Listing</h3>
                <button @click="inquiryOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="{{ route('marketplace.inquire', $listing->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Your Full Name</label>
                    <input type="text" name="name" value="{{ Auth::check() ? Auth::user()->name : '' }}" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ Auth::check() ? Auth::user()->email : '' }}" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ Auth::check() ? Auth::user()->phone : '' }}" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Message</label>
                    <textarea name="message" rows="3" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none" placeholder="I am interested in this listing. Please provide more details or financing options."></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg text-xs shadow">
                    Submit Inquiry
                </button>
            </form>
        </div>
    </div>

    <!-- Booking Modal -->
    <div x-show="bookOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="bookOpen = false">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <h3 class="font-bold text-lg text-slate-900">Book Viewing / Test Drive</h3>
                <button @click="bookOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="{{ route('marketplace.book', $listing->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Your Full Name</label>
                    <input type="text" name="name" value="{{ Auth::check() ? Auth::user()->name : '' }}" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Email</label>
                        <input type="email" name="email" value="{{ Auth::check() ? Auth::user()->email : '' }}" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ Auth::check() ? Auth::user()->phone : '' }}" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Type</label>
                        <select name="appointment_type" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none bg-white">
                            <option value="test_drive">Test Drive</option>
                            <option value="viewing">Property Viewing</option>
                            <option value="meeting">Consultation</option>
                            <option value="inspection">Inspection</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Preferred Date/Time</label>
                        <input type="datetime-local" name="start_time" required class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700 block mb-1">Special Notes</label>
                    <textarea name="notes" rows="2" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 outline-none" placeholder="Any requests or questions for the agent..."></textarea>
                </div>
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-lg text-xs shadow">
                    Confirm Appointment Request
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
