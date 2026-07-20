<!DOCTYPE html>
<html lang="en" x-data="{ dark: (localStorage.getItem('theme') ?? 'dark') === 'dark', sidebar: window.innerWidth > 1024 }"
      :class="{ 'dark': dark }" x-init="$watch('dark', v => localStorage.setItem('theme', v ? 'dark' : 'light'))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ route('manifest') }}">
    <meta name="theme-color" content="#2ca0d4">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme')
</head>
<body>
@php
    $nav = [
        ['route' => 'dashboard',        'label' => 'Dashboard',   'perm' => null,              'icon' => 'M3 12l9-9 9 9M5 10v10h14V10'],
        ['route' => 'products.index',   'label' => 'Products',    'perm' => 'products.view',   'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4'],
        ['route' => 'brands.index',     'label' => 'Brands',      'perm' => 'brands.view',     'icon' => 'M7 7h.01M7 3h5a2 2 0 011.4.6l7 7a2 2 0 010 2.8l-5 5a2 2 0 01-2.8 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z'],
        ['route' => 'categories.index', 'label' => 'Categories',  'perm' => 'categories.view', 'icon' => 'M4 6h16M4 12h16M4 18h16'],
        ['route' => 'trading-videos.index', 'label' => 'Trading Videos', 'perm' => 'trading-videos.view', 'icon' => 'M14.75 11.17l-3.2-2.13A1 1 0 0010 9.87v4.26a1 1 0 001.55.83l3.2-2.13a1 1 0 000-1.66zM21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['route' => 'banners.index',    'label' => 'Banners',     'perm' => 'banners.view',    'icon' => 'M4 5a2 2 0 012-2h12a2 2 0 012 2v14l-8-4-8 4V5z'],
        ['route' => 'batches.index',    'label' => 'Batches',     'perm' => 'batches.view',    'icon' => 'M20 7L12 3 4 7m16 0l-8 4m8-4v10l-8 4M4 7l8 4m-8-4v10l8 4m0-14v14'],
        ['route' => 'qr-codes.index',   'label' => 'QR Codes',    'perm' => 'qr-codes.view',   'icon' => 'M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h3m3 0h.01M14 14h.01M17 20h3v-3'],
        ['route' => 'wallets.index',    'label' => 'Wallets',     'perm' => 'wallets.view',    'icon' => 'M3 10h18M7 15h.01M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z'],
        ['route' => 'redemptions.index','label' => 'Redemptions', 'perm' => 'redemptions.view','icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m0-8c1.11 0 2.08.402 2.599 1M12 8c-1.11 0-2.08.402-2.599 1'],
        ['route' => 'push-notifications.index', 'label' => 'Notifications', 'perm' => 'notifications.view', 'icon' => 'M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
        ['route' => 'leads.index',      'label' => 'Leads',       'perm' => 'leads.view',      'icon' => 'M16 8a3 3 0 11-6 0 3 3 0 016 0zm-3 5a6 6 0 00-6 6h12a6 6 0 00-6-6zM19 8h4m-2-2v4'],
        ['route' => 'business-cards.index', 'label' => 'Business Cards', 'perm' => 'business-cards.view', 'icon' => 'M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7zm5 3a1.5 1.5 0 103 0 1.5 1.5 0 00-3 0zm-1 5c0-1.1 1.1-2 2.5-2s2.5.9 2.5 2M14 10h4m-4 3h4'],
        ['route' => 'users.index',      'label' => 'Users',       'perm' => 'users.view',      'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z'],
        ['route' => 'roles.index',      'label' => 'Roles',       'perm' => 'roles.view',      'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
    $locationNav = [
        ['route' => 'countries.index',  'label' => 'Countries',   'perm' => 'countries.view',  'icon' => 'M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 000 18M12 3a15 15 0 010 18M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['route' => 'states.index',     'label' => 'States',      'perm' => 'states.view',     'icon' => 'M9 20l-5.4 2.1V6.9L9 4.8m0 15.2l6-2.1m-6 2.1V4.8m6 13.1l5.4 2.1V4.8L15 6.9m0 11V6.9m0 0L9 4.8'],
        ['route' => 'cities.index',     'label' => 'Cities',      'perm' => 'cities.view',     'icon' => 'M3 21h18M5 21V7l5-4v18M14 21V11l5-3v13M9 9h.01M9 13h.01M9 17h.01'],
    ];
    $extra = [
        ['route' => 'profile.edit', 'label' => 'My Profile', 'icon' => 'M5.121 17.804A13 13 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['route' => 'my.notifications', 'label' => 'Notifications', 'icon' => 'M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
        ['route' => 'my.business-card.edit', 'label' => 'My Business Card', 'icon' => 'M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7zm5 3a1.5 1.5 0 103 0 1.5 1.5 0 00-3 0zm-1 5c0-1.1 1.1-2 2.5-2s2.5.9 2.5 2M14 10h4m-4 3h4'],
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
            <img src="{{ asset('images/logo.png') }}" alt="Saint Globle" class="w-11 h-11 rounded-xl ring-1 ring-white/20 shadow-lg">
            <div class="leading-tight text-white">
                <div class="font-display font-bold tracking-tight">Saint Globle</div>
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

            @php $visibleLocationNav = array_filter($locationNav, fn ($i) => auth()->user()->can($i['perm'])); @endphp
            @if ($visibleLocationNav !== [])
                <p class="px-3 pt-4 pb-1.5 text-[10px] font-semibold uppercase tracking-[.14em] text-white/35">Locations</p>
                @foreach ($visibleLocationNav as $item)
                    <a href="{{ route($item['route']) }}" class="lux-nav flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ $isActive($item['route']) ? 'active' : '' }}">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            @endif

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
                @php
                    $unreadCount = auth()->user()->unreadNotificationCount();
                    $recentNotifications = auth()->user()->notificationRecipients()
                        ->with('pushNotification:id,title,body,action_url')
                        ->latest()->limit(6)->get();
                    // Only offer the opt-in to a user with no registered device.
                    $needsPushOptIn = auth()->user()->pushSubscriptions()->doesntExist();
                @endphp
                <div x-data="{ open: false, unread: {{ $unreadCount }} }"
                     x-on:notification-received.window="unread++" class="relative">
                    <button @click="open = !open" title="Notifications" aria-label="Notifications"
                            class="relative p-2.5 rounded-xl border border-[var(--border)] hover:border-brand-400 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-show="unread > 0" x-cloak x-text="unread > 9 ? '9+' : unread"
                              class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 grid place-items-center rounded-full bg-rose-500 text-white text-[10px] font-bold"></span>
                    </button>

                    <div x-show="open" x-cloak x-transition @click.outside="open = false"
                         class="lux-card lux-pop absolute right-0 mt-2 w-80 max-w-[calc(100vw_-_2rem)] py-2 text-sm max-h-[70vh] overflow-y-auto">
                        <div class="px-4 py-2 flex items-center justify-between">
                            <span class="font-semibold">Notifications</span>
                            <span x-show="unread > 0" x-cloak class="text-xs text-[var(--muted)]" x-text="unread + ' unread'"></span>
                        </div>
                        <div class="lux-divider my-1"></div>

                        @forelse ($recentNotifications as $row)
                            <a href="{{ route('my.notifications.read', $row) }}"
                               class="block px-4 py-2.5 hover:bg-black/5 dark:hover:bg-white/5 rounded-lg mx-1 {{ $row->read_at ? 'opacity-60' : '' }}">
                                <div class="flex items-start gap-2">
                                    @unless ($row->read_at)
                                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-brand-500 shrink-0"></span>
                                    @endunless
                                    <div class="min-w-0">
                                        <p class="font-medium truncate">{{ $row->pushNotification?->title }}</p>
                                        <p class="text-xs text-[var(--muted)] line-clamp-2">{{ $row->pushNotification?->body }}</p>
                                        <p class="mt-0.5 text-[11px] text-[var(--muted)]">{{ $row->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="px-4 py-6 text-center text-[var(--muted)]">No notifications yet.</p>
                        @endforelse

                        @if ($needsPushOptIn)
                            <div class="lux-divider my-1"></div>
                            <div class="px-4 py-2">
                                <button type="button" onclick="enablePushNotifications(this)"
                                        class="w-full rounded-lg lux-btn text-white text-xs font-medium px-3 py-2">
                                    Enable push notifications
                                </button>
                            </div>
                        @endif

                        <div class="lux-divider my-1"></div>
                        <a href="{{ route('my.notifications') }}" class="block px-4 py-2 text-center text-brand-500 font-medium hover:underline">View all</a>
                    </div>
                </div>

                <button @click="dark = !dark" class="p-2.5 rounded-xl border border-[var(--border)] hover:border-brand-400 transition" title="Toggle theme">
                    <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="dark" x-cloak class="w-5 h-5 text-gold" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2.5 pl-1.5 pr-3 py-1.5 rounded-xl border border-[var(--border)] hover:border-brand-400 transition">
                        <div class="w-8 h-8 rounded-lg grid place-items-center text-sm font-bold text-white" style="background:linear-gradient(135deg,#2ca0d4,#1b5a7c)">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        <span class="hidden sm:block text-sm font-semibold">{{ \Illuminate\Support\Str::of(auth()->user()->name)->words(2, '') }}</span>
                    </button>
                    <div x-show="open" x-cloak x-transition @click.outside="open = false" class="lux-card lux-pop absolute right-0 mt-2 w-60 max-w-[calc(100vw_-_2rem)] py-2 text-sm">
                        <div class="px-4 py-2">
                            <div class="font-semibold truncate">{{ auth()->user()->email }}</div>
                            <div class="text-xs text-[var(--muted)]">{{ auth()->user()->getRoleNames()->implode(', ') ?: 'No role' }}</div>
                        </div>
                        <div class="lux-divider my-1"></div>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-black/5 dark:hover:bg-white/5 rounded-lg mx-1">My Profile</a>
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
@include('partials.support-buttons')
@include('partials.toast')
@include('partials.push')
@include('partials.select2')
{{-- Page scripts run last so jQuery / Select2 above are already available. --}}
@stack('scripts')
</body>
</html>
