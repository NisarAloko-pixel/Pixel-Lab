<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Pixel Lab') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">

        <script>
        // Set theme + sidebar state before first paint, straight from localStorage,
        // so there's no flash of the wrong theme / sidebar width while the page loads.
        (function () {
            var stored = localStorage.getItem('theme');
            var theme = (stored === 'dark' || stored === 'light')
                ? stored
                : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);

            var sidebar = localStorage.getItem('sidebar') || 'full';
            document.documentElement.setAttribute('data-sidebar', sidebar);
        })();
    </script>
   @vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="stylesheet" href="{{ asset('css/pixel-theme.css') }}?v=4">
<style>
    [x-cloak] { display: none !important; }

    /* Sidebar collapse (html[data-sidebar="collapsed"]) */
    /* Smooth collapse animation */
    aside { overflow-x: hidden; transition: width .28s cubic-bezier(.4, 0, .2, 1); }
    aside .side-item {
        white-space: nowrap; overflow: hidden;
        transition: font-size .28s cubic-bezier(.4, 0, .2, 1), padding .28s cubic-bezier(.4, 0, .2, 1),
                    background .15s ease, color .15s ease;
    }
    aside .side-logo { transition: width .28s cubic-bezier(.4, 0, .2, 1), height .28s cubic-bezier(.4, 0, .2, 1); }

    /* Toggle button animation */
    #sidebarToggle { transition: transform .15s ease, background .15s ease, box-shadow .15s ease; }
    #sidebarToggle:hover { background: var(--paper); box-shadow: 0 2px 8px -2px rgba(33,38,47,0.25); transform: translateY(-1px); }
    #sidebarToggle:active { transform: scale(.9); }
    #sidebarToggle .bar {
        transform-box: fill-box; transform-origin: left center;
        transition: transform .28s cubic-bezier(.4, 0, .2, 1);
    }
    html[data-sidebar="collapsed"] #sidebarToggle .bar-top { transform: scaleX(.55); }
    html[data-sidebar="collapsed"] #sidebarToggle .bar-bot { transform: scaleX(.75); }
    html[data-sidebar="collapsed"] #sidebarToggle .bar-mid { transform: scaleX(1.05); }

    /* Hide the sidebar scrollbar (it still scrolls with wheel/touch) */
    aside { scrollbar-width: none; -ms-overflow-style: none; }
    aside::-webkit-scrollbar { display: none; width: 0; height: 0; }
    html[data-sidebar="collapsed"] aside { width: 4.5rem !important; }
    html[data-sidebar="collapsed"] aside .side-item {
        font-size: 0 !important;
        justify-content: center !important;
        padding-left: 0 !important; padding-right: 0 !important;
    }
    html[data-sidebar="collapsed"] aside .side-item > span { gap: 0 !important; }
    html[data-sidebar="collapsed"] aside .side-item svg.transition-transform { display: none !important; }
    html[data-sidebar="collapsed"] aside .side-chevron-btn { display: none !important; }
    html[data-sidebar="collapsed"] aside [x-show] { display: none !important; }
    html[data-sidebar="collapsed"] aside .side-logo { width: 2.5rem !important; height: 2.5rem !important; }
</style>
</head>
<body style="background:var(--paper); color:var(--ink);">
     @include('partials.pixel-loader')
    
<div class="flex min-h-screen">

  <aside class="w-60 p-3 space-y-1 shrink-0 sticky top-0 h-screen overflow-y-auto" style="background:var(--side-bg); box-shadow: 4px 0 16px -8px rgba(0,0,0,0.35);">
    <div class="flex items-center justify-center px-2 mb-5 pt-2">
    <img src="{{ asset('images/logo.png') }}" alt="Pixel Lab" class="side-logo w-24 h-24 rounded-lg object-contain">
</div>

    <a href="{{ route('dashboard') }}" title="Dashboard" class="side-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>
        </svg>
        Dashboard
    </a>

    @can('sales.view')
        <a href="{{ route('sales.index') }}" title="Sales" class="side-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            Sales
        </a>
    @endcan

    @can('customers.view')
        <a href="{{ route('customers.index') }}" title="Customers" class="side-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Customers
        </a>
    @endcan

    @can('products.view')
        <a href="{{ route('products.index') }}" title="Products" class="side-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
            Products
        </a>
    @endcan

    @can('purchases.view')
        <a href="{{ route('purchases.index') }}" title="Purchases" class="side-item {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 14l2 2 4-4"/>
            </svg>
            Purchases
        </a>
    @endcan

    @can('filament.view')
        <a href="{{ route('filament.index') }}" title="Filament Stock" class="side-item {{ request()->routeIs('filament.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v6"/><circle cx="12" cy="14" r="7"/><circle cx="12" cy="14" r="3"/>
            </svg>
            Filament Stock
        </a>
    @endcan

    @can('supplies.view')
        <a href="{{ route('supplies.index') }}" title="Supplies" class="side-item {{ request()->routeIs('supplies.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/>
            </svg>
            Supplies
        </a>
    @endcan

    @can('suppliers.view')
        <a href="{{ route('suppliers.index') }}" title="Suppliers" class="side-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
            Suppliers
        </a>
    @endcan

    @can('orders.view')
        <a href="{{ route('orders.index') }}" title="Orders" class="side-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
            Orders
        </a>
    @endcan

    @can('queue.view')
        <a href="{{ route('queue.index') }}" title="Queue Jobs" class="side-item {{ request()->routeIs('queue.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="4" rx="1"/><rect x="3" y="10" width="18" height="4" rx="1"/><rect x="3" y="16" width="10" height="4" rx="1"/>
            </svg>
            Queue Jobs
        </a>
    @endcan

    @can('printers.view')
        <a href="{{ route('printers.index') }}" title="Printers" class="side-item {{ request()->routeIs('printers.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Printers
        </a>
    @endcan

    @php $financeOpen = request()->routeIs('finance.*') || request()->routeIs('expenses.*'); @endphp
    @canany(['balance.view', 'expenses.view', 'transaction.view', 'advance.view'])
        <div x-data="{ open: {{ $financeOpen ? 'true' : 'false' }} }">
            <button @click="open = !open" title="Finance" class="side-item w-full justify-between {{ $financeOpen ? 'active' : '' }}">
                <span class="flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    Finance
                </span>
                <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 7l5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <div x-show="open" x-cloak x-transition class="mt-1 space-y-0.5">
                @can('balance.view')
                    <a href="{{ route('finance.balance') }}" class="side-sub {{ request()->routeIs('finance.balance*', 'finance.shareholders.*') ? 'active' : '' }}">Balance</a>
                @endcan
                @can('expenses.view')
                    <a href="{{ route('expenses.index') }}" class="side-sub {{ request()->routeIs('expenses.*') ? 'active' : '' }}">Expenses</a>
                @endcan
                @can('transaction.view')
                    <a href="{{ route('finance.transaction') }}" class="side-sub {{ request()->routeIs('finance.transaction*') ? 'active' : '' }}">Transaction</a>
                @endcan
                @can('advance.view')
                    <a href="{{ route('finance.advance') }}" class="side-sub {{ request()->routeIs('finance.advance*', 'finance.employee.*') ? 'active' : '' }}">Advance</a>
                @endcan
            </div>
        </div>
    @endcanany

    @can('reports.view')
        @php $reportsOpen = request()->routeIs('reports.*'); @endphp
        <div x-data="{ open: {{ $reportsOpen ? 'true' : 'false' }} }">
            <div class="flex items-center">
                <a href="{{ route('reports.index') }}" title="Reports" class="side-item flex-1 {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>
                    </svg>
                    Reports
                </a>
                <button @click="open = !open" class="side-item side-chevron-btn px-2" style="min-width:auto;">
                    <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 7l5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div x-show="open" x-cloak x-transition class="mt-1 space-y-0.5">
                <a href="{{ route('reports.sales') }}" class="side-sub {{ request()->routeIs('reports.sales') ? 'active' : '' }}">Sales report</a>
                <a href="{{ route('reports.profit') }}" class="side-sub {{ request()->routeIs('reports.profit') ? 'active' : '' }}">Profit report</a>
                <a href="{{ route('reports.product-profit') }}" class="side-sub {{ request()->routeIs('reports.product-profit') ? 'active' : '' }}">Product profit</a>
                <a href="{{ route('reports.expenses') }}" class="side-sub {{ request()->routeIs('reports.expenses') ? 'active' : '' }}">Expenses report</a>
                <a href="{{ route('reports.purchases') }}" class="side-sub {{ request()->routeIs('reports.purchases*') ? 'active' : '' }}">Purchases report</a>
            </div>
        </div>
    @endcan

    @can('employees.view')
        <a href="{{ route('employees.index') }}" title="Employees" class="side-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="17" y1="11" x2="23" y2="11"/>
            </svg>
            Employees
        </a>
    @endcan

@php $settingsOpen = request()->routeIs('profile.*') || request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('settings.*') || request()->routeIs('audit-logs.*'); @endphp
    <div class="pt-3 mt-3" style="border-top:1px solid var(--side-line);" x-data="{ open: {{ $settingsOpen ? 'true' : 'false' }} }">
        <button @click="open = !open" title="Settings" class="side-item w-full justify-between {{ $settingsOpen ? 'active' : '' }}">
            <span class="flex items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                Settings
            </span>
            <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 7l5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <div x-show="open" x-cloak x-transition class="mt-1 space-y-0.5">
            <a href="{{ route('profile.edit') }}" class="side-sub {{ request()->routeIs('profile.*') ? 'active' : '' }}">Profile</a>

            @can('users.view')
                <a href="{{ route('users.index') }}" class="side-sub {{ request()->routeIs('users.*') ? 'active' : '' }}">Users</a>
            @endcan

            @can('audit.view')
                <a href="{{ route('audit-logs.index') }}" class="side-sub {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">Audit Log</a>
            @endcan

            @can('roles.manage')
                <a href="{{ route('roles.index') }}" class="side-sub {{ request()->routeIs('roles.*') ? 'active' : '' }}">Roles</a>
            @endcan

            @can('settings.view')
                <a href="{{ route('settings.edit') }}" class="side-sub {{ request()->routeIs('settings.*') ? 'active' : '' }}">App settings</a>
            @endcan
        </div>
    </div>

    <a href="{{ route('about') }}" title="About" class="side-item {{ request()->routeIs('about') ? 'active' : '' }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        About
    </a>
</aside>
<script>
// Keep the sidebar scroll position between page loads
(function () {
    var aside = document.querySelector('aside');
    if (!aside) return;
    var KEY = 'sidebarScroll';
    function restore() {
        var y = null;
        try { y = sessionStorage.getItem(KEY); } catch (e) {}
        if (y !== null) aside.scrollTop = parseInt(y, 10) || 0;
    }
    restore();
    document.addEventListener('alpine:initialized', restore);
    window.addEventListener('load', restore);
    aside.addEventListener('scroll', function () {
        try { sessionStorage.setItem(KEY, String(aside.scrollTop)); } catch (e) {}
    }, { passive: true });
})();
</script>
    <main class="flex-1 min-w-0 flex flex-col min-h-screen">
        {{-- Topbar: sidebar toggle + theme toggle + user avatar with a dropdown for Profile / Log out --}}
        <header class="flex justify-end items-center gap-3 px-6 py-3 sticky top-0 z-20" style="border-bottom:1px solid var(--line); background:var(--surface);">

            {{-- Sidebar toggle: collapses the sidebar to icons only, click again to expand --}}
            <button type="button" id="sidebarToggle" title="Toggle sidebar"
                class="w-9 h-9 rounded-full flex items-center justify-center"
                style="margin-right:auto; border:1px solid var(--line); color:var(--ink-soft);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line class="bar bar-top" x1="3" y1="6" x2="21" y2="6"/><line class="bar bar-mid" x1="3" y1="12" x2="21" y2="12"/><line class="bar bar-bot" x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>

<div x-data="{ dark: document.documentElement.getAttribute('data-theme') === 'dark' }">
                <button @click="
                        dark = !dark;
                        document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
                        localStorage.setItem('theme', dark ? 'dark' : 'light');
                    "
                    title="Toggle light / dark mode"
                    class="w-9 h-9 rounded-full flex items-center justify-center"
                    style="border:1px solid var(--line); color:var(--ink-soft);">
                    <svg x-show="!dark" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                    <svg x-show="dark" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"/>
                        <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                        <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                    </svg>
                </button>
            </div>

            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" class="flex items-center gap-3">
                    <span class="text-sm font-medium" style="color:var(--ink);">Welcome, {{ auth()->user()->name }}</span>
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                        class="w-12 h-12 rounded-full object-cover" style="border:1px solid var(--line); background:var(--accent-soft);">
                </button>

                <div x-show="open" x-transition style="display:none; background:var(--surface); border:1px solid var(--line);"
                    class="absolute right-0 mt-2 w-44 rounded-md shadow-lg z-50">
                    <a href="{{ route('profile.edit') }}"
                        class="menu-link block px-4 py-2.5 text-sm" style="color:var(--ink);">
                        Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}" onsubmit="window.__loggingOut = true">
    @csrf
    <button type="submit" class="menu-link w-full text-left px-4 py-2.5 text-sm" style="color:var(--danger);">
        Log out
    </button>
</form>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="mx-6 mt-6 mb-0 rounded-md px-4 py-3 text-sm" style="background:var(--success-soft); color:var(--success); border:1px solid var(--line);">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mx-6 mt-6 mb-0 rounded-md px-4 py-3 text-sm" style="background:var(--danger-soft); color:var(--danger); border:1px solid var(--line);">
                {{ session('error') }}
            </div>
        @endif
        {{ $slot }}
                @include('partials.footer')
    </main>
</div>

<script>
// Sidebar collapse toggle (state is restored in <head> before first paint)
(function () {
    var btn = document.getElementById('sidebarToggle');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var root = document.documentElement;
        var next = root.getAttribute('data-sidebar') === 'collapsed' ? 'full' : 'collapsed';
        root.setAttribute('data-sidebar', next);
        localStorage.setItem('sidebar', next);
    });
})();
</script>

{{-- Live filtering: search forms, tab links and pagination update the results without a page reload --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const results  = () => document.querySelector('[data-live-results]');
    const tabsEl   = () => document.querySelector('[data-live-tabs]');
    const tabNames = () => (tabsEl()?.dataset.liveTabs || 'status').split(',').map(s => s.trim()).filter(Boolean);

    let timer = null;
    let controller = null;

    async function load(url) {
        controller?.abort();
        controller = new AbortController();
        try {
            const res  = await fetch(url, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const doc  = new DOMParser().parseFromString(await res.text(), 'text/html');
            const fresh = doc.querySelector('[data-live-results]');
            if (!fresh || !results()) { location.href = url; return; }

            results().innerHTML = fresh.innerHTML;

// Scripts inserted through innerHTML don't run on their own, so re-run them (redraws the charts)
results().querySelectorAll('script').forEach((old) => {
    const s = document.createElement('script');
    [...old.attributes].forEach((a) => s.setAttribute(a.name, a.value));
    s.textContent = old.textContent;
    old.replaceWith(s);
});

            const freshTabs = doc.querySelector('[data-live-tabs]');
            if (tabsEl() && freshTabs) tabsEl().innerHTML = freshTabs.innerHTML;

            history.replaceState(null, '', url);
        } catch (e) {
            if (e.name !== 'AbortError') location.href = url; // fall back to a normal load
        }
    }

    // Build the URL for a search form, keeping whichever tab is active
    function formUrl(form) {
        const params = new URLSearchParams(new FormData(form));
        for (const [k, v] of [...params]) if (v === '') params.delete(k);

        const current = new URLSearchParams(location.search);
        tabNames().forEach((n) => {
            params.delete(n);
            if (current.get(n)) params.set(n, current.get(n));
        });

        const url = new URL(form.action || location.href, location.href);
        url.search = params.toString();
        return url;
    }

    // Search forms
    document.querySelectorAll('form[data-live-search]').forEach((form) => {
        form.addEventListener('input', (e) => {
            if (e.target.matches('input[type=text], input[type=search], input:not([type])')) {
                clearTimeout(timer);
                timer = setTimeout(() => load(formUrl(form)), 250);
            }
        });
        form.addEventListener('change', (e) => {
            if (e.target.matches('select, input[type=checkbox]')) {
                clearTimeout(timer);
                load(formUrl(form));
            }
        });
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            clearTimeout(timer);
            load(formUrl(form));
        });
    });

    // Tab links and pagination links
    document.addEventListener('click', (e) => {
        if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey) return;
        const a = e.target.closest('[data-live-tabs] a[href], [data-live-results] nav a[href], [data-live-results] .pill-group a[href]');
        if (!a || !results()) return;

        e.preventDefault();
        const url = new URL(a.href, location.href);

        // Tab click: carry over whatever is typed in the search form
        if (a.closest('[data-live-tabs]')) {
            const form = document.querySelector('form[data-live-search]');
            if (form) {
                const names = tabNames();
                new URLSearchParams(new FormData(form)).forEach((v, k) => {
                    if (v !== '' && !names.includes(k) && !url.searchParams.has(k)) url.searchParams.set(k, v);
                });
            }
        }

        clearTimeout(timer);
        load(url);
    });
});
</script>
 @include('partials.confirm-modal')
</body>
</html>
