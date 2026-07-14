@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    @php
        $cards = [
            ['label' => 'Products',        'value' => $stats['products'],      'color' => 'brand'],
            ['label' => 'Batches',         'value' => $stats['batches'],       'color' => 'indigo'],
            ['label' => 'QR Codes',        'value' => $stats['qr_codes'],      'color' => 'violet'],
            ['label' => 'Verified',        'value' => $stats['verified'],      'color' => 'emerald'],
            ['label' => 'Verifications',   'value' => $stats['verifications'], 'color' => 'sky'],
            ['label' => 'Scans',           'value' => $stats['scans'],         'color' => 'cyan'],
            ['label' => 'Fraud Suspected', 'value' => $stats['fraud_scans'],   'color' => 'rose'],
            ['label' => 'Reward Points',   'value' => $stats['reward_points'], 'color' => 'amber'],
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
