@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    @php
        $cards = [
            ['label' => 'Products',        'value' => $stats['products'],      'color' => 'brand'],
            ['label' => 'Batches',         'value' => $stats['batches'],       'color' => 'indigo'],
            ['label' => 'QR Codes',        'value' => $stats['qr_codes'],      'color' => 'violet'],
            ['label' => 'Verifications',   'value' => $stats['verifications'], 'color' => 'sky'],
        ];
        $maxSeries = max(1, $series->max('count'));
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($cards as $c)
            <div class="lux-card p-4">
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ $c['label'] }}</div>
                <div class="mt-1 text-2xl font-bold text-{{ $c['color'] }}-600 dark:text-{{ $c['color'] }}-400">{{ number_format($c['value']) }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-4 mt-6">
        <div class="lg:col-span-2 lux-card p-5">
            <h2 class="font-semibold mb-4">Verifications — last 14 days</h2>
            <div class="flex items-end gap-1.5 h-48">
                @foreach ($series as $point)
                    <div class="flex-1 flex flex-col items-center gap-1 group">
                        <div class="w-full rounded-t bg-brand-500/80 hover:bg-brand-500 transition-all relative"
                             style="height: {{ max(4, (int) round($point['count'] / $maxSeries * 160)) }}px" title="{{ $point['count'] }}">
                            <span class="absolute -top-5 left-1/2 -translate-x-1/2 text-xs opacity-0 group-hover:opacity-100">{{ $point['count'] }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 rotate-0 whitespace-nowrap">{{ \Illuminate\Support\Str::before($point['label'], ' ') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lux-card p-5">
            <h2 class="font-semibold mb-4">Scan results</h2>
            @php $totalScans = max(1, $resultBreakdown->sum()); @endphp
            <div class="space-y-3">
                @forelse (['valid'=>'emerald','duplicate'=>'amber','invalid'=>'rose','blocked'=>'slate','expired'=>'orange'] as $res => $color)
                    @php $val = (int) ($resultBreakdown[$res] ?? 0); @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1"><span class="capitalize">{{ $res }}</span><span class="font-medium">{{ $val }}</span></div>
                        <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-{{ $color }}-500" style="width: {{ round($val / $totalScans * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No scans yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    @if ($userPanel || $pointPanel)
        <div class="grid lg:grid-cols-2 gap-4 mt-6">
            {{-- ---------------- User management ---------------- --}}
            @if ($userPanel)
                <div class="lux-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold">User Management</h2>
                        <a href="{{ route('users.index') }}" class="text-xs font-medium text-brand-500 hover:underline">Manage &rarr;</a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach ([
                            ['Total', $userPanel['total'], 'brand'],
                            ['Active', $userPanel['active'], 'emerald'],
                            ['Suspended', $userPanel['suspended'], 'rose'],
                            ['New this month', $userPanel['new_this_month'], 'sky'],
                        ] as [$label, $value, $color])
                            <div class="rounded-xl border border-[var(--border)] px-3 py-2.5">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">{{ $label }}</div>
                                <div class="mt-0.5 text-xl font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ number_format($value) }}</div>
                            </div>
                        @endforeach
                    </div>

                    @if ($userPanel['roles']->isNotEmpty())
                        @php $roleMax = max(1, $userPanel['roles']->max()); @endphp
                        <h3 class="mt-5 mb-2.5 text-xs font-semibold uppercase tracking-wide text-slate-400">By role</h3>
                        <div class="space-y-2.5">
                            @foreach ($userPanel['roles']->take(6) as $role => $count)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>{{ ucwords(str_replace('-', ' ', $role)) }}</span>
                                        <span class="font-medium">{{ number_format($count) }}</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                        <div class="h-full rounded-full bg-brand-500" style="width: {{ round($count / $roleMax * 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <h3 class="mt-5 mb-2.5 text-xs font-semibold uppercase tracking-wide text-slate-400">Newest users</h3>
                    <div class="space-y-2.5">
                        @forelse ($userPanel['recent'] as $u)
                            <a href="{{ route('users.show', $u) }}" class="flex items-center justify-between gap-3 text-sm hover:bg-black/5 dark:hover:bg-white/5 rounded-lg -mx-2 px-2 py-1.5 transition">
                                <div class="min-w-0">
                                    <div class="font-medium truncate">{{ $u->name }}</div>
                                    <div class="text-xs text-slate-400 truncate">
                                        {{ $u->getRoleNames()->first() ?? 'no role' }}@if ($u->city) · {{ $u->city->name }}@endif
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400 shrink-0">{{ $u->created_at?->diffForHumans() }}</span>
                            </a>
                        @empty
                            <p class="text-sm text-slate-400">No users yet.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- ---------------- Point management ---------------- --}}
            @if ($pointPanel)
                <div class="lux-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold">Point Management</h2>
                        <a href="{{ route('wallets.index') }}" class="text-xs font-medium text-brand-500 hover:underline">Wallets &rarr;</a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ([
                            ['Points issued', $pointPanel['issued'], 'emerald'],
                            ['Redeemed', $pointPanel['redeemed'], 'violet'],
                            ['Outstanding', $pointPanel['outstanding'], 'amber'],
                        ] as [$label, $value, $color])
                            <div class="rounded-xl border border-[var(--border)] px-3 py-2.5">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">{{ $label }}</div>
                                <div class="mt-0.5 text-xl font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ number_format($value, 2) }}</div>
                            </div>
                        @endforeach
                    </div>

                    @php
                        $issued = max(0.01, $pointPanel['issued']);
                        $redeemedPct = min(100, round($pointPanel['redeemed'] / $issued * 100));
                    @endphp
                    <div class="mt-4">
                        <div class="flex justify-between text-xs text-slate-400 mb-1.5">
                            <span>Redeemed vs issued</span><span>{{ $redeemedPct }}%</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-violet-500" style="width: {{ $redeemedPct }}%"></div>
                        </div>
                        <p class="mt-1.5 text-[11px] text-slate-400">
                            {{ number_format($pointPanel['wallets']) }} wallet(s)@if ($pointPanel['frozen'] > 0) · <span class="text-rose-500">{{ $pointPanel['frozen'] }} frozen</span>@endif
                        </p>
                    </div>

                    <h3 class="mt-5 mb-2.5 text-xs font-semibold uppercase tracking-wide text-slate-400">Redemption requests</h3>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <a href="{{ route('redemptions.index') }}?status=pending" class="rounded-xl border border-amber-300/60 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-900/20 px-2 py-2.5 hover:border-amber-400 transition">
                            <div class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ number_format($pointPanel['pending_count']) }}</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Pending</div>
                            <div class="text-[11px] font-medium">{{ number_format($pointPanel['pending_amount'], 2) }} pts</div>
                        </a>
                        <a href="{{ route('redemptions.index') }}?status=approved" class="rounded-xl border border-[var(--border)] px-2 py-2.5 hover:border-emerald-400 transition">
                            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($pointPanel['approved_count']) }}</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Approved</div>
                            <div class="text-[11px] font-medium">{{ number_format($pointPanel['approved_amount'], 2) }} pts</div>
                        </a>
                        <a href="{{ route('redemptions.index') }}?status=rejected" class="rounded-xl border border-[var(--border)] px-2 py-2.5 hover:border-rose-400 transition">
                            <div class="text-lg font-bold text-rose-600 dark:text-rose-400">{{ number_format($pointPanel['rejected_count']) }}</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">Rejected</div>
                        </a>
                    </div>

                    <h3 class="mt-5 mb-2.5 text-xs font-semibold uppercase tracking-wide text-slate-400">Top point holders</h3>
                    <div class="space-y-2.5">
                        @forelse ($pointPanel['top_earners'] as $wallet)
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <div class="min-w-0">
                                    <div class="font-medium truncate">{{ $wallet->user?->name ?? 'Unknown' }}</div>
                                    <div class="text-xs text-slate-400">{{ $wallet->user?->phone }}</div>
                                </div>
                                <span class="shrink-0 px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 font-medium">
                                    {{ number_format((float) $wallet->balance, 2) }} pts
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">No points earned yet.</p>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-4 mt-6">
        <div class="lux-card p-5">
            <h2 class="font-semibold mb-4">Top products</h2>
            <div class="space-y-3">
                @forelse ($topProducts as $tp)
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <div class="font-medium">{{ $tp->product?->name ?? 'Unknown' }}</div>
                            <div class="text-xs text-slate-400">{{ $tp->product?->sku }}</div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 font-medium">{{ $tp->verifications }} verified</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No verifications yet.</p>
                @endforelse
            </div>
        </div>

        <div class="lux-card p-5">
            <h2 class="font-semibold mb-4">Recent activity</h2>
            <div class="space-y-3">
                @forelse ($recentActivity as $log)
                    <div class="flex gap-3 text-sm">
                        <div class="w-2 h-2 mt-1.5 rounded-full bg-brand-500 shrink-0"></div>
                        <div class="min-w-0">
                            <div class="truncate">{{ $log->description ?? $log->event }}</div>
                            <div class="text-xs text-slate-400">{{ $log->causer?->name ?? 'System' }} · {{ $log->created_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No activity recorded.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
