@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6 bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div class="text-center">
            <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-xl font-bold mx-auto mb-3">
                Z
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Sign In to Your Account</h2>
            <p class="text-xs text-slate-500 mt-1">Access Super Admin, Dealer CRM, or Customer Dashboard</p>
        </div>

        <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Email or Phone Number</label>
                <input type="text" name="login" value="{{ old('login') }}" required autofocus placeholder="admin@zacma.com or +251..." class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none focus:border-blue-600">
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="text-xs font-semibold text-slate-700">Password</label>
                </div>
                <input type="password" name="password" required placeholder="••••••••" class="w-full text-xs border border-slate-300 rounded-xl p-3 outline-none focus:border-blue-600">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center space-x-2 text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-sm shadow transition">
                Sign In
            </button>
        </form>

        <div class="pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-800 ml-1">Create an account</a>
        </div>
    </div>
</div>
@endsection
