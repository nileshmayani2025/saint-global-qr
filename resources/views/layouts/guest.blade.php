<!DOCTYPE html>
<html lang="en" x-data="{ dark: (localStorage.getItem('theme') ?? 'dark') === 'dark' }" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') · {{ config('app.name') }}</title>
    <link rel="icon" href="/images/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <div class="min-h-screen grid lg:grid-cols-[1.05fr_1fr]">
        <!-- Brand showcase -->
        <div class="relative hidden lg:flex flex-col justify-between p-14 overflow-hidden text-white"
             style="background:radial-gradient(700px 420px at 80% 0%, rgba(67,180,224,.5), transparent 55%), linear-gradient(160deg,#0e2f45,#0c2438 55%,#081726);">
            <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full" style="background:radial-gradient(circle, rgba(217,178,95,.18), transparent 65%)"></div>
            <div class="absolute bottom-10 -left-16 w-80 h-80 rounded-full" style="background:radial-gradient(circle, rgba(44,160,212,.22), transparent 65%)"></div>

            <div class="relative flex items-center gap-3.5">
                <img src="/images/logo.png" alt="Saint Globe" class="w-14 h-14 rounded-2xl ring-1 ring-white/20 shadow-xl">
                <div class="leading-tight">
                    <div class="font-display font-bold text-xl">Saint Globe</div>
                    <div class="text-xs text-white/55">A Construction Chemicals</div>
                </div>
            </div>

            <div class="relative">
                <span class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[.16em] text-gold mb-5">
                    <span class="w-6 h-px bg-gold"></span> Verify · Reward · Protect
                </span>
                <h2 class="font-display text-4xl font-bold leading-[1.15]">The premium platform for<br>authentic products.</h2>
                <p class="mt-5 text-white/70 max-w-md leading-relaxed">QR-based anti-counterfeit verification, batch intelligence and consumer rewards — engineered into one elegant, secure ERP.</p>
                <div class="mt-9 flex gap-8">
                    <div><div class="font-display text-2xl font-bold">100%</div><div class="text-xs text-white/55">Tamper-proof QR</div></div>
                    <div><div class="font-display text-2xl font-bold">Real-time</div><div class="text-xs text-white/55">Fraud detection</div></div>
                    <div><div class="font-display text-2xl font-bold">Instant</div><div class="text-xs text-white/55">Reward payouts</div></div>
                </div>
            </div>

            <p class="relative text-white/45 text-xs">&copy; {{ date('Y') }} Saint Globe · A Construction Chemicals</p>
        </div>

        <!-- Form -->
        <div class="flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-md lux-rise">
                @include('partials.flash')
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
