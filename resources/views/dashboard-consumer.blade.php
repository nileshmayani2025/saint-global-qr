@extends('layouts.consumer')
@section('title', 'Home')

@php
    $actions = [
        ['route' => 'scan',        'label' => 'Scan &<br>Verify',      'icon' => 'M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h3m3 0h.01M14 14h.01M17 20h3v-3'],
        ['route' => 'my.scans',    'label' => 'Product<br>History',    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['route' => 'my.rewards',  'label' => 'Rewards<br>History',    'icon' => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zM5 12h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7'],
        ['route' => 'verify.form', 'label' => 'Enter<br>Code',         'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
@endphp

@section('content')
    {{-- Shown over the home screen on first open, and again after a new video. --}}
    @include('partials.trading-video-popup')

    {{-- Banner carousel --}}
    @if ($banners->isNotEmpty())
        <div x-data="{
                 active: 0,
                 count: {{ $banners->count() }},
                 timer: null,
                 start() { if (this.count > 1) this.timer = setInterval(() => this.active = (this.active + 1) % this.count, 5000) },
                 stop() { clearInterval(this.timer) },
                 go(i) { this.stop(); this.active = i; this.start() },
             }"
             x-init="start()" @mouseenter="stop()" @mouseleave="start()" class="mb-5">
            <div class="relative">
                @foreach ($banners as $i => $banner)
                    {{-- x-cloak, not a "hidden" class: x-show only toggles inline display, so a
                         class-based hide would win and the slide could never be revealed. --}}
                    <div x-show="active === {{ $i }}" x-transition:enter.duration.400ms x-transition:enter.opacity.0
                         x-cloak class="relative overflow-hidden rounded-2xl">
                        @if ($banner->image_path)
                            <img src="{{ asset('media/'.$banner->image_path) }}" alt=""
                                 class="absolute inset-0 w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-950/55 to-transparent"></div>
                        @endif

                        <div @class([
                                'relative p-5 min-h-[150px] flex flex-col justify-center',
                                'bg-gradient-to-br from-brand-600 to-brand-800' => ! $banner->image_path,
                                'bg-slate-900' => (bool) $banner->image_path,
                            ])>
                            <h2 class="font-display text-lg font-bold text-white leading-snug max-w-[75%]">{{ $banner->title }}</h2>
                            @if ($banner->subtitle)
                                <p class="mt-1 text-sm text-white/75 max-w-[75%]">{{ $banner->subtitle }}</p>
                            @endif
                            @if ($banner->button_label)
                                <a href="{{ $banner->link_url ?: route('scan') }}"
                                   class="mt-3 inline-flex w-max items-center gap-1.5 rounded-lg bg-white text-slate-900 text-xs font-semibold px-3.5 py-2 shadow hover:bg-white/90 transition">
                                    {{ $banner->button_label }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($banners->count() > 1)
                <div class="flex justify-center gap-1.5 mt-3">
                    @foreach ($banners as $i => $banner)
                        <button @click="go({{ $i }})" aria-label="Slide {{ $i + 1 }}"
                                class="h-1.5 rounded-full transition-all"
                                :class="active === {{ $i }} ? 'w-5 bg-brand-500' : 'w-1.5 bg-slate-300 dark:bg-slate-700'"></button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- Points / wallet summary --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="lux-card p-3.5 text-center">
            <div class="text-xl font-bold text-brand-600 dark:text-brand-400">{{ number_format($stats['total_scans']) }}</div>
            <div class="mt-0.5 text-[11px] leading-tight text-[var(--muted)]">Products<br>Verified</div>
        </div>
        <div class="lux-card p-3.5 text-center">
            <div class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($stats['points_earned']) }}</div>
            <div class="mt-0.5 text-[11px] leading-tight text-[var(--muted)]">Reward<br>Points</div>
        </div>
        <div class="lux-card p-3.5 text-center">
            <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">₹{{ number_format($stats['balance'], 2) }}</div>
            <div class="mt-0.5 text-[11px] leading-tight text-[var(--muted)]">vCash<br>Earned</div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="lux-card mt-4 p-4">
        <div class="grid grid-cols-4 gap-2">
            @foreach ($actions as $action)
                <a href="{{ route($action['route']) }}" class="flex flex-col items-center gap-2 group">
                    <span class="w-12 h-12 rounded-2xl grid place-items-center bg-brand-600 text-white shadow-sm group-hover:bg-brand-700 group-active:scale-95 transition">
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $action['icon'] }}"/>
                        </svg>
                    </span>
                    <span class="text-[11px] leading-tight text-center text-[var(--muted)] font-medium">{!! $action['label'] !!}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Product list --}}
    <div class="mt-5">
        <div class="rounded-xl bg-brand-500/10 px-3.5 py-2 mb-3">
            <h2 class="text-xs font-bold tracking-wide text-brand-700 dark:text-brand-300">OUR PRODUCTS</h2>
        </div>

        <div class="grid grid-cols-2 gap-3">
            @forelse ($products as $product)
                <div class="lux-card p-3">
                    @if ($product->image_path)
                        <img src="{{ asset('media/'.$product->image_path) }}" alt=""
                             class="w-full aspect-square object-cover rounded-xl mb-2.5">
                    @else
                        <div class="w-full aspect-square rounded-xl mb-2.5 grid place-items-center bg-brand-500/10 text-brand-600 dark:text-brand-300 font-display font-bold text-2xl">
                            {{ strtoupper(substr($product->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="text-sm font-semibold leading-tight line-clamp-2">{{ $product->name }}</div>
                    <div class="mt-1 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-[var(--muted)]">{{ $product->sku }}</span>
                        @if ((int) $product->reward_points > 0)
                            <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-700 dark:text-amber-300 shrink-0">
                                +{{ $product->reward_points }} pts
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="col-span-2 lux-card p-6 text-center text-sm text-[var(--muted)]">No products listed yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent scans --}}
    @if ($recentScans->isNotEmpty())
        <div class="mt-5">
            <div class="rounded-xl bg-brand-500/10 px-3.5 py-2 mb-3 flex items-center justify-between">
                <h2 class="text-xs font-bold tracking-wide text-brand-700 dark:text-brand-300">RECENT SCANS</h2>
                <a href="{{ route('my.scans') }}" class="text-[11px] font-semibold text-brand-600 dark:text-brand-400">View more</a>
            </div>
            <div class="lux-card divide-y divide-[var(--border)]">
                @foreach ($recentScans as $log)
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <div class="min-w-0">
                            <div class="text-sm font-medium truncate">{{ $log->product?->name ?? 'Product' }}</div>
                            <div class="text-[11px] text-[var(--muted)]">{{ $log->verified_at?->diffForHumans() }}</div>
                        </div>
                        @if ((int) $log->reward_points > 0)
                            <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-amber-500/15 text-amber-700 dark:text-amber-300 shrink-0">
                                +{{ $log->reward_points }} pts
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
