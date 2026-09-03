@extends('layouts.admin')

@section('title', 'Dealership Branding & Profile')

@section('content')
<div class="max-w-2xl space-y-6">
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Organization Profile & Branding</h2>
        <p class="text-xs text-slate-500">Configure your business details, default currency, logo, and marketplace branding</p>
    </div>

    <form action="{{ route('dealer.settings.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        @csrf

        <div>
            <label class="text-xs font-semibold text-slate-700 block mb-1">Dealership / Business Name</label>
            <input type="text" name="name" value="{{ $org->name }}" required class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Contact Email</label>
                <input type="email" name="email" value="{{ $org->email }}" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Contact Phone</label>
                <input type="text" name="phone" value="{{ $org->phone }}" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">City / Region</label>
                <input type="text" name="city" value="{{ $org->city }}" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Default Currency</label>
                <select name="currency" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none bg-white">
                    <option value="ETB" {{ $org->currency === 'ETB' ? 'selected' : '' }}>ETB (Ethiopian Birr)</option>
                    <option value="USD" {{ $org->currency === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-700 block mb-1">Physical Address</label>
            <input type="text" name="address" value="{{ $org->address }}" placeholder="Showroom / Office address" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none">
        </div>

        <div class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-100">
            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Brand Primary Accent Color</label>
                <input type="color" name="primary_color" value="{{ $org->branding_colors['primary'] ?? '#2563EB' }}" class="w-16 h-10 border border-slate-300 rounded-lg p-1">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Upload Dealership Logo</label>
                <input type="file" name="logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-blue-50 file:text-blue-700">
            </div>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl text-xs shadow transition mt-2">
            Save Branding Changes
        </button>
    </form>
</div>
@endsection
