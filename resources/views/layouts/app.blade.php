<!DOCTYPE html>
<html lang="en" x-data="{ dark: (localStorage.getItem('theme') ?? 'dark') === 'dark', sidebar: window.innerWidth > 1024 }"
      :class="{ 'dark': dark }" x-init="$watch('dark', v => localStorage.setItem('theme', v ? 'dark' : 'light'))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
@php
    $nav = [
        ['route' => 'dashboard',        'label' => 'Dashboard',   'perm' => null,              'icon' => 'M3 12l9-9 9 9M5 10v10h14V10'],
        ['route' => 'products.index',   'label' => 'Products',    'perm' => 'products.view',   'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4'],
        ['route' => 'brands.index',     'label' => 'Brands',      'perm' => 'brands.view',     'icon' => 'M7 7h.01M7 3h5a2 2 0 011.4.6l7 7a2 2 0 010 2.8l-5 5a2 2 0 01-2.8 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z'],
        ['route' => 'categories.index', 'label' => 'Categories',  'perm' => 'categories.view', 'icon' => 'M4 6h16M4 12h16M4 18h16'],
        ['route' => 'batches.index',    'label' => 'Batches',     'perm' => 'batches.view',    'icon' => 'M20 7L12 3 4 7m16 0l-8 4m8-4v10l-8 4M4 7l8 4m-8-4v10l8 4m0-14v14'],
        ['route' => 'qr-codes.index',   'label' => 'QR Codes',    'perm' => 'qr-codes.view',   'icon' => 'M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h3m3 0h.01M14 14h.01M17 20h3v-3'],
        ['route' => 'wallets.index',    'label' => 'Wallets',     'perm' => 'wallets.view',    'icon' => 'M3 10h18M7 15h.01M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z'],
        ['route' => 'redemptions.index','label' => 'Redemptions', 'perm' => 'redemptions.view','icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m0-8c1.11 0 2.08.402 2.599 1M12 8c-1.11 0-2.08.402-2.599 1'],
        ['route' => 'users.index',      'label' => 'Users',       'perm' => 'users.view',      'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z'],
        ['route' => 'roles.index',      'label' => 'Roles',       'perm' => 'roles.view',      'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
    $extra = [
        ['route' => 'scan',        'label' => 'Scan QR',    'icon' => 'M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h3m3 0h.01M14 14h.01M17 20h3v-3'],
        ['route' => 'my.scans',    'label' => 'My Scans',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['route' => 'my.rewards',  'label' => 'My Rewards', 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
    ];
    $isActive = fn ($route) => request()->routeIs($route) || request()->routeIs(\Illuminate\Support\Str::before($route, '.').'.*');
@endphp

<div class="min-h-screen lg:flex">
    <!-- Sidebar -->
    <div x-show="sidebar && window.innerWidth <= 1024" x-cloak @click="sidebar=false" class="fixed inset-0 z-30 bg-slate-950/60 lg:hidden"></div>
    <aside x-show="sidebar" x-cloak x-transition:enter.duration.200ms
           class="lux-sidebar fixed inset-y-0 left-0 z-40 w-[264px] flex flex-col lg:static lg:translate-x-0">
        <div class="h-[70px] flex items-center gap-3 px-5">
            <img src="{{ asset('images/logo.png') }}" alt="Saint Globe" class="w-11 h-11 rounded-xl ring-1 ring-white/20 shadow-lg">
            <div class="leading-tight text-white">
                <div class="font-display font-bold tracking-tight">Saint Globe</div>
                <div class="text-[11px] text-white/50 font-medium">Construction Chemicals</div>
            </div>
        </div>
        <div class="mx-5 lux-divider opacity-40"></div>

        <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-1">
            <p class="px-3 pb-1.5 text-[10px] font-semibold uppercase tracking-[.14em] text-white/35">Management</p>
            @foreach ($nav as $item)
                @if (is_null($item['perm']) || auth()->user()->can($item['perm']))
                    <a href="{{ route($item['route']) }}" class="lux-nav flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ $isActive($item['route']) ? 'active' : '' }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach

            <p class="px-3 pt-4 pb-1.5 text-[10px] font-semibold uppercase tracking-[.14em] text-white/35">My account</p>
            @foreach ($extra as $item)
                <a href="{{ route($item['route']) }}" class="lux-nav flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ $isActive($item['route']) ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
            <a href="{{ route('verify.form') }}" target="_blank" class="lux-nav flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Verify a Product
            </a>
        </nav>

        <div class="p-4">
            <div class="rounded-2xl p-4 bg-white/5 border border-white/10 text-center">
                <div class="text-[11px] text-white/50">Signed in as</div>
                <div class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                <div class="mt-0.5 text-[11px] text-gold">{{ auth()->user()->getRoleNames()->first() ?? 'member' }}</div>
            </div>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="lux-topbar h-[70px] flex items-center gap-3 px-4 sm:px-6 sticky top-0 z-20">
            <button @click="sidebar = !sidebar" class="p-2 rounded-xl hover:bg-black/5 dark:hover:bg-white/5 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="font-display font-bold text-xl">@yield('title', 'Dashboard')</h1>
            <div class="ml-auto flex items-center gap-2">
                @unless (auth()->user()->isApproved())
                    <span class="hidden sm:inline text-xs px-3 py-1.5 rounded-full bg-amber-500/15 text-amber-600 dark:text-amber-300 font-semibold ring-1 ring-amber-500/25">Pending approval</span>
                @endunless
                <button @click="dark = !dark" class="p-2.5 rounded-xl border border-[var(--border)] hover:border-brand-400 transition" title="Toggle theme">
                    <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="dark" x-cloak class="w-5 h-5 text-gold" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2.5 pl-1.5 pr-3 py-1.5 rounded-xl border border-[var(--border)] hover:border-brand-400 transition">
                        <div class="w-8 h-8 rounded-lg grid place-items-center text-sm font-bold text-white" style="background:linear-gradient(135deg,#2ca0d4,#1b5a7c)">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        <span class="hidden sm:block text-sm font-semibold">{{ \Illuminate\Support\Str::of(auth()->user()->name)->words(2, '') }}</span>
                    </button>
                    <div x-show="open" x-cloak x-transition @click.outside="open = false" class="lux-card absolute right-0 mt-2 w-60 py-2 text-sm">
                        <div class="px-4 py-2">
                            <div class="font-semibold truncate">{{ auth()->user()->email }}</div>
                            <div class="text-xs text-[var(--muted)]">{{ auth()->user()->getRoleNames()->implode(', ') ?: 'No role' }}</div>
                        </div>
                        <div class="lux-divider my-1"></div>
                        <a href="{{ route('my.rewards') }}" class="block px-4 py-2 hover:bg-black/5 dark:hover:bg-white/5 rounded-lg mx-1">My Rewards</a>
                        <a href="{{ route('my.scans') }}" class="block px-4 py-2 hover:bg-black/5 dark:hover:bg-white/5 rounded-lg mx-1">My Scans</a>
                        <div class="lux-divider my-1"></div>
                        <form method="POST" action="{{ route('logout') }}" class="mx-1">@csrf
                            <button class="w-full text-left px-4 py-2 text-rose-500 font-medium hover:bg-rose-500/10 rounded-lg">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 w-full lux-rise">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
@include('partials.select2')
</body>
</html>
