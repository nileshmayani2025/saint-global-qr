{{--
    The public digital business card.

    Standalone rather than extending a layout: it is handed to people who have
    nothing to do with the panel, so there is no nav, no sidebar and no login
    prompt — just the card.
--}}
@php
    $phone = \App\Support\Phone::normalize($owner->phone);
    $whatsapp = $card->whatsappNumber();
    $email = $card->contactEmail();
    $photo = $card->photoUrl();
    $location = collect([$owner->city?->name, $owner->state?->name])->filter()->implode(', ');
    $role = $owner->getRoleNames()->first();
@endphp
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $owner->name }}@if ($card->business_name) · {{ $card->business_name }}@endif</title>
    <meta name="description" content="{{ $card->tagline ?: 'Digital business card' }}">
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <meta name="theme-color" content="#0e1a2b">

    {{-- Open Graph so the link previews properly when shared on WhatsApp. --}}
    <meta property="og:title" content="{{ $owner->name }}">
    <meta property="og:description" content="{{ $card->business_name ?: ($card->tagline ?: 'Digital business card') }}">
    @if ($photo)<meta property="og:image" content="{{ $photo }}">@endif
    <meta property="og:type" content="profile">

    @vite(['resources/css/app.css'])
    @include('partials.theme')
</head>
<body class="min-h-screen">
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">

    <div class="w-full max-w-sm">
        <div class="lux-card overflow-hidden">
            {{-- Header --}}
            <div class="relative px-6 pt-8 pb-6 text-center overflow-hidden"
                 style="background:linear-gradient(150deg,#2ca0d4,#1b5a7c)">

                {{-- The photo fills the header as a backdrop rather than sitting
                     in its own circle, with a tinted wash over it so the white
                     text and the QR stay readable on any picture. --}}
                @if ($photo)
                    <img src="{{ $photo }}" alt="" aria-hidden="true"
                         class="absolute inset-0 w-full h-full object-cover scale-105">
                    <div class="absolute inset-0"
                         style="background:linear-gradient(150deg,rgba(44,160,212,.82),rgba(20,70,100,.92))"></div>
                @else
                    <span aria-hidden="true"
                          class="absolute inset-0 grid place-items-center font-display font-bold text-white/20 select-none leading-none"
                          style="font-size:12rem">{{ strtoupper(substr($owner->name, 0, 1)) }}</span>
                @endif

                <div class="relative">
                    <img src="{{ route('card.qr', $card->slug) }}" alt="QR code for {{ $owner->name }}"
                         class="w-28 h-28 mx-auto rounded-xl bg-white p-2 shadow-xl" loading="lazy">

                    <h1 class="mt-4 font-display font-bold text-xl text-white drop-shadow">{{ $owner->name }}</h1>

                    @if ($card->business_name)
                        <p class="mt-0.5 text-sm font-semibold text-white/90 drop-shadow">{{ $card->business_name }}</p>
                    @elseif ($role)
                        <p class="mt-0.5 text-sm text-white/80 drop-shadow">{{ ucwords(str_replace('-', ' ', $role)) }}</p>
                    @endif

                    @if ($card->tagline)
                        <p class="mt-2 text-xs text-white/75 leading-relaxed drop-shadow">{{ $card->tagline }}</p>
                    @endif
                </div>
            </div>

            {{-- Details --}}
            <div class="px-6 py-5 space-y-3.5 text-sm">
                @if ($phone !== '')
                    <a href="tel:+91{{ $phone }}" class="flex items-center gap-3 hover:text-brand-500 transition">
                        <span class="w-9 h-9 shrink-0 grid place-items-center rounded-lg bg-brand-500/10 text-brand-500">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11 11 0 005.5 5.5l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"/>
                            </svg>
                        </span>
                        <span class="font-medium">+91 {{ \App\Support\Phone::format($phone) }}</span>
                    </a>
                @endif

                @if ($email)
                    <a href="mailto:{{ $email }}" class="flex items-center gap-3 hover:text-brand-500 transition">
                        <span class="w-9 h-9 shrink-0 grid place-items-center rounded-lg bg-brand-500/10 text-brand-500">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <span class="font-medium break-all">{{ $email }}</span>
                    </a>
                @endif

                @if ($location !== '' || $owner->address)
                    <div class="flex items-start gap-3">
                        <span class="w-9 h-9 shrink-0 grid place-items-center rounded-lg bg-brand-500/10 text-brand-500">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>
                        <span class="pt-1.5 text-[var(--muted)]">
                            {{ collect([$owner->address, $location])->filter()->implode(', ') }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="px-6 pb-5 grid grid-cols-2 gap-2.5">
                @if ($whatsapp !== '')
                    <a href="https://wa.me/91{{ $whatsapp }}" target="_blank" rel="noopener"
                       class="flex items-center justify-center gap-2 rounded-xl bg-[#25D366] text-white font-semibold py-2.5 text-sm hover:opacity-90 transition">
                        <svg class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.86 9.86 0 004.79 1.22c5.46 0 9.9-4.44 9.9-9.9 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0012.04 2zm5.43 12.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.29-.76.95-.94 1.15-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.65-2.05-.17-.29-.02-.45.13-.6.14-.13.3-.35.44-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.6-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.29-1.04 1.01-1.04 2.47s1.06 2.86 1.21 3.06c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.7.63.71.23 1.36.19 1.87.12.57-.09 1.75-.72 2-1.41.25-.69.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35z"/></svg>
                        WhatsApp
                    </a>
                @endif

                @if ($phone !== '')
                    <a href="tel:+91{{ $phone }}"
                       class="flex items-center justify-center gap-2 rounded-xl lux-btn text-white font-semibold py-2.5 text-sm">
                        Call
                    </a>
                @endif

            </div>
        </div>

        <div class="mt-5 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="" class="w-8 h-8 mx-auto rounded-lg opacity-70">
            <p class="mt-1.5 text-[11px] text-[var(--muted)]">Powered by {{ config('app.name') }}</p>
        </div>
    </div>
</div>
</body>
</html>
