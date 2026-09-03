@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4 py-16 text-center">
    <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
        <i class="fa-solid fa-xmark"></i>
    </div>
    <h1 class="text-2xl font-extrabold text-slate-900 mb-2">Payment Cancelled</h1>
    <p class="text-xs text-slate-500 mb-6">
        Your payment attempt was cancelled or could not be completed. No funds have been captured.
    </p>

    <div class="flex justify-center space-x-3">
        <a href="{{ route('home') }}" class="px-5 py-2.5 bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-300">
            Home
        </a>
        <a href="{{ route('marketplace.browse') }}" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-semibold hover:bg-blue-700">
            Browse More Listings
        </a>
    </div>
</div>
@endsection
