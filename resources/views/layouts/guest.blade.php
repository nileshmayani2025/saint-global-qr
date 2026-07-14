<!DOCTYPE html>
<html lang="en" x-data="{ dark: (localStorage.getItem('theme') ?? 'dark') === 'dark' }" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center p-5 sm:p-8"
         style="background:radial-gradient(760px 460px at 50% -10%, rgba(44,160,212,.18), transparent 60%), radial-gradient(600px 380px at 50% 120%, rgba(217,178,95,.08), transparent 60%);">
        <div class="w-full max-w-md lux-rise">
            <div class="text-center mb-7">
                <img src="{{ asset('images/logo.png') }}" alt="Saint Globe" class="w-20 h-20 mx-auto rounded-2xl ring-1 ring-black/5 dark:ring-white/10 shadow-xl">
                <div class="mt-3 font-display font-bold text-xl">Saint Globe</div>
                <div class="text-xs text-[var(--muted)]">A Construction Chemicals</div>
            </div>

            <div class="lux-card p-7 sm:p-8">
                @include('partials.flash')
                @yield('content')
            </div>

            <p class="mt-6 text-center text-xs text-[var(--muted)]">&copy; {{ date('Y') }} Saint Globe · A Construction Chemicals</p>
        </div>
    </div>
</body>
</html>
