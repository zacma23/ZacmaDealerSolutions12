<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Portal') - Zacma AI Platform</title>

    <script src="{{ asset('js/tailwind.min.js') }}"></script>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex overflow-hidden font-sans text-slate-800 antialiased" x-data="{ sidebarOpen: false }">
    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-200 ease-in-out lg:static lg:translate-x-0">
        <!-- Brand -->
        <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800">
            <a href="{{ route('home') }}" class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-base">Z</div>
                <span class="font-bold text-white tracking-tight">Zacma Cloud</span>
            </a>
        </div>

        <!-- Tenant Badge -->
        <div class="px-6 py-3 bg-slate-900/50 border-b border-slate-800/80">
            <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Active Tenant</div>
            <div class="text-sm font-semibold text-white truncate">
                {{ Auth::user()->isSuperAdmin() ? 'Global Super Admin' : (Auth::user()->organization ? Auth::user()->organization->name : 'No Tenant') }}
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
            @if(Auth::user()->isSuperAdmin())
                <div class="text-[11px] uppercase tracking-wider text-slate-500 font-semibold px-3 py-1">Super Admin</div>
                <a href="{{ route('super-admin.dashboard') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('super-admin.dashboard') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-chart-pie w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('super-admin.organizations.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('super-admin.organizations.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-building w-5"></i>
                    <span>Organizations</span>
                </a>
                <a href="{{ route('super-admin.categories.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('super-admin.categories.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-layer-group w-5"></i>
                    <span>Dynamic Categories</span>
                </a>
                <a href="{{ route('super-admin.plans.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('super-admin.plans.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-credit-card w-5"></i>
                    <span>SaaS Plans & Limits</span>
                </a>
                <a href="{{ route('super-admin.crm.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('super-admin.crm.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-users w-5"></i>
                    <span>Global CRM</span>
                </a>
                <a href="{{ route('super-admin.listings.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('super-admin.listings.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-tags w-5"></i>
                    <span>Global Listings</span>
                </a>
                <a href="{{ route('super-admin.settings.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('super-admin.settings.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-sliders w-5"></i>
                    <span>Integrations & Keys</span>
                </a>
                <a href="{{ route('super-admin.audit-logs.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('super-admin.audit-logs.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-shield-halved w-5"></i>
                    <span>Audit Logs</span>
                </a>
            @endif

            @if(Auth::user()->isStaff() || Auth::user()->isSuperAdmin())
                <div class="text-[11px] uppercase tracking-wider text-slate-500 font-semibold px-3 py-1 mt-4">Dealer / Business CRM</div>
                <a href="{{ route('dealer.dashboard') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('dealer.dashboard') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-gauge w-5"></i>
                    <span>Overview</span>
                </a>
                <a href="{{ route('dealer.crm.contacts.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('dealer.crm.contacts.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-address-book w-5"></i>
                    <span>Contacts & 360</span>
                </a>
                <a href="{{ route('dealer.crm.leads.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('dealer.crm.leads.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-fire w-5 text-amber-400"></i>
                    <span>Leads & AI Scoring</span>
                </a>
                <a href="{{ route('dealer.crm.pipeline.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('dealer.crm.pipeline.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-columns w-5 text-blue-400"></i>
                    <span>Sales Pipeline (Kanban)</span>
                </a>
                <a href="{{ route('dealer.crm.tasks.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('dealer.crm.tasks.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-list-check w-5"></i>
                    <span>Tasks & Follow-ups</span>
                </a>
                <a href="{{ route('dealer.crm.calendar.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('dealer.crm.calendar.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-calendar-days w-5"></i>
                    <span>Calendar & Visits</span>
                </a>
                <a href="{{ route('dealer.ai.assistant') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('dealer.ai.assistant') ? 'bg-indigo-600 text-white' : 'text-indigo-300' }}">
                    <i class="fa-solid fa-robot w-5"></i>
                    <span>AI CRM Assistant</span>
                </a>

                <div class="text-[11px] uppercase tracking-wider text-slate-500 font-semibold px-3 py-1 mt-4">Inventory & Sales</div>
                <a href="{{ route('dealer.listings.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('dealer.listings.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-box-archive w-5"></i>
                    <span>Listings / Inventory</span>
                </a>
                <a href="{{ route('dealer.orders.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('dealer.orders.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-receipt w-5"></i>
                    <span>Orders & Deposits</span>
                </a>
                <a href="{{ route('dealer.staff.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('dealer.staff.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-user-group w-5"></i>
                    <span>Staff Team</span>
                </a>
                <a href="{{ route('dealer.settings.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('dealer.settings.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-gear w-5"></i>
                    <span>Branding & Profile</span>
                </a>
                <a href="{{ route('dealer.subscription.index') }}" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-slate-800 hover:text-white {{ request()->routeIs('dealer.subscription.*') ? 'bg-blue-600 text-white' : '' }}">
                    <i class="fa-solid fa-credit-card w-5 text-emerald-400"></i>
                    <span>Subscription & Plans</span>
                </a>
            @endif
        </nav>

        <!-- User profile footer -->
        <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-between">
            <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 group overflow-hidden" title="Account & Profile Settings">
                <img src="{{ Auth::user()->getAvatarUrl() }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 flex-shrink-0 group-hover:border-blue-500 transition">
                <div class="text-xs truncate">
                    <div class="font-semibold text-white truncate max-w-[110px] group-hover:text-blue-400 transition">{{ Auth::user()->name }}</div>
                    <div class="text-slate-400 text-[10px] flex items-center gap-1">
                        <span>{{ Auth::user()->role }}</span>
                        <i class="fa-solid fa-gear text-[9px] text-slate-500 group-hover:text-blue-400"></i>
                    </div>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-slate-400 hover:text-red-400 p-1.5" title="Logout">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main View Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Topbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-slate-500 hover:text-slate-700">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h1 class="text-lg font-bold text-slate-900">@yield('title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('home') }}" target="_blank" class="text-xs font-semibold text-slate-600 hover:text-blue-600 flex items-center space-x-1 border border-slate-200 rounded-lg px-3 py-1.5">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Public Marketplace</span>
                </a>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                    {{ Auth::user()->role }}
                </span>
                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2 pl-3 border-l border-slate-200 hover:opacity-85 transition" title="My Profile Settings">
                    <img src="{{ Auth::user()->getAvatarUrl() }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-sm">
                    <span class="hidden md:inline text-xs font-bold text-slate-700">{{ Auth::user()->name }}</span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 hidden md:inline"></i>
                </a>
            </div>
        </header>

        <!-- Flash alerts -->
        @if(session('success'))
            <div class="bg-emerald-50 border-b border-emerald-200 text-emerald-800 px-6 py-2.5 text-sm flex items-center space-x-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border-b border-rose-200 text-rose-800 px-6 py-2.5 text-sm flex items-center space-x-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Scrollable Body -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    <!-- Universal Contextual AI Assistant Widget -->
    <x-ai-chat-widget />
</body>
</html>
