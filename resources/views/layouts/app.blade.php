<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zacma AI Platform')</title>

    <!-- Tailwind & Alpine Standalone Bundles (cPanel & offline compatible) -->
    <script src="{{ asset('js/tailwind.min.js') }}"></script>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <style>
        [x-cloak] { display: none !important; }
        :root {
            --primary: {{ isset($currentTenant) && isset($currentTenant->branding_colors['primary']) ? $currentTenant->branding_colors['primary'] : '#2563EB' }};
        }
    </style>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased">
    @if(session()->has('impersonator_id'))
        <div class="bg-amber-500 text-slate-950 px-4 py-2 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-md z-50 sticky top-0">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-user-secret text-base"></i>
                <span><strong>Impersonation Active:</strong> Viewing platform as <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->email }} &bull; Role: {{ Auth::user()->role }}).</span>
            </div>
            <a href="{{ route('super-admin.stop-impersonation') }}" class="bg-slate-950 hover:bg-black text-white px-3 py-1 rounded text-xs font-bold transition flex items-center space-x-1 shadow-sm">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Exit & Return to Super Admin</span>
            </a>
        </div>
    @endif
    <!-- Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-6">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3">
                        @if(isset($currentTenant) && $currentTenant->logo)
                            <img src="{{ asset('storage/' . $currentTenant->logo) }}" alt="{{ $currentTenant->name }}" class="h-9 w-auto">
                        @else
                            <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                                Z
                            </div>
                        @endif
                        <span class="font-bold text-xl tracking-tight text-slate-900">
                            {{ isset($currentTenant) ? $currentTenant->name : 'Zacma Marketplace' }}
                        </span>
                    </a>

                    <nav class="hidden md:flex space-x-4">
                        <a href="{{ route('home') }}" class="text-sm font-medium text-slate-600 hover:text-blue-600 px-3 py-2">Home</a>
                        <a href="{{ route('marketplace.browse') }}" class="text-sm font-medium text-slate-600 hover:text-blue-600 px-3 py-2">Browse All</a>
                        <a href="{{ route('marketplace.category', 'vehicles') }}" class="text-sm font-medium text-slate-600 hover:text-blue-600 px-3 py-2">Vehicles</a>
                        <a href="{{ route('marketplace.category', 'property') }}" class="text-sm font-medium text-slate-600 hover:text-blue-600 px-3 py-2">Property</a>
                        <a href="{{ route('marketplace.category', 'electronics') }}" class="text-sm font-medium text-slate-600 hover:text-blue-600 px-3 py-2">Electronics</a>
                        <a href="{{ route('pricing') }}" class="text-sm font-medium text-slate-600 hover:text-blue-600 px-3 py-2">Dealer Pricing</a>
                    </nav>
                </div>

                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        <a href="{{ Auth::user()->getDashboardUrl() }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800 flex items-center space-x-1">
                            <i class="fa-solid fa-gauge"></i>
                            <span>Portal Dashboard</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2 pl-3 border-l border-slate-200 hover:opacity-80 transition" title="Profile Settings">
                            <img src="{{ Auth::user()->getAvatarUrl() }}" alt="{{ Auth::user()->name }}" class="w-7 h-7 rounded-full object-cover border border-slate-200">
                            <span class="text-xs font-bold text-slate-700">{{ Auth::user()->name }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-slate-400 hover:text-red-600 ml-1" title="Logout">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-700 hover:text-blue-600 px-3 py-2">Log in</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm">
                            Register
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex md:hidden">
                    <button @click="mobileOpen = !mobileOpen" class="text-slate-600 hover:text-slate-900 p-2">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileOpen" x-cloak class="md:hidden border-t border-slate-200 bg-white px-4 pt-2 pb-4 space-y-2">
            <a href="{{ route('home') }}" class="block px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 rounded">Home</a>
            <a href="{{ route('marketplace.browse') }}" class="block px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 rounded">Browse All</a>
            @auth
                <div class="flex items-center gap-3 px-3 py-2 bg-slate-50 rounded-lg border border-slate-100">
                    <img src="{{ Auth::user()->getAvatarUrl() }}" alt="{{ Auth::user()->name }}" class="w-9 h-9 rounded-full object-cover border">
                    <div class="text-xs">
                        <div class="font-bold text-slate-900">{{ Auth::user()->name }}</div>
                        <div class="text-slate-500 text-[10px]">{{ Auth::user()->role }}</div>
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-base font-semibold text-slate-700 hover:bg-slate-50 rounded">Profile & Security Settings</a>
                <a href="{{ Auth::user()->getDashboardUrl() }}" class="block px-3 py-2 text-base font-semibold text-blue-600 hover:bg-blue-50 rounded">Portal Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-base font-medium text-red-600 hover:bg-red-50 rounded">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 rounded">Log in</a>
                <a href="{{ route('register') }}" class="block px-3 py-2 text-base font-medium text-blue-600 hover:bg-blue-50 rounded">Register</a>
            @endauth
        </div>
    </header>

    <!-- Global Flash Alerts -->
    @if(session('success'))
        <div class="bg-emerald-50 border-b border-emerald-200 text-emerald-800 px-4 py-3 text-sm flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border-b border-rose-200 text-rose-800 px-4 py-3 text-sm flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-sm">Z</div>
                    <span class="text-white font-bold text-lg">Zacma Platform</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Enterprise multi-tenant marketplace and CRM SaaS platform supporting automotive, real estate, electronics, and commercial businesses.
                </p>
                <div class="flex space-x-4 text-slate-400 text-sm">
                    <i class="fa-brands fa-facebook hover:text-white cursor-pointer"></i>
                    <i class="fa-brands fa-telegram hover:text-white cursor-pointer"></i>
                    <i class="fa-brands fa-linkedin hover:text-white cursor-pointer"></i>
                    <i class="fa-brands fa-whatsapp hover:text-white cursor-pointer"></i>
                </div>
            </div>
            <div>
                <h4 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Marketplace</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('marketplace.browse') }}" class="hover:text-white">All Listings</a></li>
                    <li><a href="{{ route('marketplace.category', 'vehicles') }}" class="hover:text-white">Vehicles</a></li>
                    <li><a href="{{ route('marketplace.category', 'property') }}" class="hover:text-white">Real Estate</a></li>
                    <li><a href="{{ route('marketplace.category', 'electronics') }}" class="hover:text-white">Electronics</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">SaaS Solutions</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('login') }}" class="hover:text-white">Dealer CRM Portal</a></li>
                    <li><a href="{{ route('super-admin.dashboard') }}" class="hover:text-white">Super Admin</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white">Become a Seller</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Supported Payments</h4>
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="bg-slate-800 text-slate-300 px-2.5 py-1 rounded">SantimPay</span>
                    <span class="bg-slate-800 text-slate-300 px-2.5 py-1 rounded">Telebirr</span>
                    <span class="bg-slate-800 text-slate-300 px-2.5 py-1 rounded">Chapa</span>
                    <span class="bg-slate-800 text-slate-300 px-2.5 py-1 rounded">PayPal</span>
                    <span class="bg-slate-800 text-slate-300 px-2.5 py-1 rounded">Card / Stripe</span>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 pt-8 border-t border-slate-800 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} Zacma Technologies. Production Multi-Tenant Marketplace & CRM SaaS. All rights reserved.
    </footer>

    <!-- Universal AI Assistant Widget -->
    <x-ai-chat-widget />
</body>
</html>
