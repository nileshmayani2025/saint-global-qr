@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    @php $approved = auth()->user()->isApproved(); @endphp

    {{-- Scan call-to-action --}}
    <div class="lux-card p-6 sm:p-8 relative overflow-hidden bg-gradient-to-br from-brand-600 to-brand-800 text-white">
        <div class="relative z-10">
            <h2 class="font-display text-2xl font-bold">Scan a product QR</h2>
            <p class="mt-1 text-white/80 text-sm max-w-md">
                Point your camera at the QR label on a Saint Global product to verify it is genuine and earn reward points.
            </p>

            @if ($approved)
                <a href="{{ route('scan') }}"
                   class="mt-5 inline-flex items-center gap-2.5 rounded-xl bg-white text-brand-700 font-semibold px-6 py-3 shadow-lg hover:bg-white/90 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h3m3 0h.01M14 14h.01M17 20h3v-3"/>
                    </svg>
                    Scan QR Code
                </a>
            @else
                <div class="mt-5 inline-flex items-center gap-2 rounded-xl bg-amber-400/20 ring-1 ring-amber-200/40 px-4 py-3 text-sm font-medium text-amber-50">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    Your account is pending approval. Scanning unlocks once an admin approves you.
                </div>
            @endif
        </div>
        <svg class="absolute -right-6 -bottom-6 w-44 h-44 text-white/10" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h3m3 0h.01M14 14h.01M17 20h3v-3"/>
        </svg>
    </div>

    {{-- Wallet + reward points --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <div class="lux-card p-4">
            <div class="text-sm text-slate-500 dark:text-slate-400">Wallet Balance</div>
            <div class="mt-1 text-2xl font-bold text-brand-600 dark:text-brand-400">₹{{ number_format($stats['balance'], 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Redeemable: ₹{{ number_format($stats['redeemable'], 2) }}</div>
        </div>
        <div class="lux-card p-4">
            <div class="text-sm text-slate-500 dark:text-slate-400">Reward Points</div>
            <div class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($stats['points_earned']) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Earned from scans</div>
        </div>
        <div class="lux-card p-4">
            <div class="text-sm text-slate-500 dark:text-slate-400">Total Scans</div>
            <div class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($stats['total_scans']) }}</div>
        </div>
        <div class="lux-card p-4">
            <div class="text-sm text-slate-500 dark:text-slate-400">Redemptions</div>
            <div class="mt-1 text-2xl font-bold text-violet-600 dark:text-violet-400">{{ number_format($stats['redemptions_count']) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">{{ $stats['redemptions_pending'] }} pending</div>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="grid sm:grid-cols-3 gap-4 mt-6">
        <a href="{{ route('my.rewards') }}" class="lux-card p-5 flex items-center gap-3 hover:border-brand-400 transition">
            <span class="w-10 h-10 rounded-xl grid place-items-center bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </span>
            <div>
                <div class="font-semibold text-sm">My Rewards</div>
                <div class="text-xs text-slate-400">Balance &amp; payouts</div>
            </div>
        </a>
        <a href="{{ route('my.scans') }}" class="lux-card p-5 flex items-center gap-3 hover:border-brand-400 transition">
            <span class="w-10 h-10 rounded-xl grid place-items-center bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </span>
            <div>
                <div class="font-semibold text-sm">My Scans</div>
                <div class="text-xs text-slate-400">Verification history</div>
            </div>
        </a>
        <a href="{{ route('scan') }}" class="lux-card p-5 flex items-center gap-3 hover:border-brand-400 transition {{ $approved ? '' : 'opacity-50 pointer-events-none' }}">
            <span class="w-10 h-10 rounded-xl grid place-items-center bg-violet-50 dark:bg-violet-900/30 text-violet-600 dark:text-violet-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h3m3 0h.01M14 14h.01M17 20h3v-3"/></svg>
            </span>
            <div>
                <div class="font-semibold text-sm">Scan QR</div>
                <div class="text-xs text-slate-400">Verify &amp; earn</div>
            </div>
        </a>
    </div>

    {{-- Recent scans --}}
    <div class="lux-card p-5 mt-6">
        <h2 class="font-semibold mb-4">Recent scans</h2>
        <div class="space-y-3">
            @forelse ($recentScans as $log)
                <div class="flex items-center justify-between text-sm">
                    <div class="min-w-0">
                        <div class="font-medium truncate">{{ $log->product?->name ?? 'Product' }}</div>
                        <div class="text-xs text-slate-400">{{ $log->verified_at?->diffForHumans() }}</div>
                    </div>
                    @if ((int) $log->reward_points > 0)
                        <span class="px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 font-medium shrink-0">+{{ $log->reward_points }} pts</span>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-400">No scans yet. Tap <span class="font-medium">Scan QR Code</span> to get started.</p>
            @endforelse
        </div>
    </div>
@endsection
