@extends('layouts.app')
@section('title', 'Batch ' . $batch->code)

@section('content')
    <a href="{{ route('batches.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to batches</a>

    <div class="mt-4 grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-bold">{{ $batch->code }}</h2>
                        <p class="text-slate-500">{{ $batch->product?->name }} · <x-badge :status="$batch->status" /></p>
                    </div>
                    @can('batches.update')<a href="{{ route('batches.edit', $batch) }}" class="text-sm text-brand-600 hover:underline">Edit</a>@endcan
                </div>
                <dl class="mt-6 grid sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-slate-400">Brand</dt><dd class="font-medium">{{ $batch->product?->brand?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">Quantity</dt><dd class="font-medium">{{ number_format($batch->quantity) }}</dd></div>
                    <div><dt class="text-slate-400">Manufacture date</dt><dd class="font-medium">{{ optional($batch->manufacture_date)->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">Expiry date</dt><dd class="font-medium">{{ optional($batch->expiry_date)->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">Reward points</dt><dd class="font-medium">{{ $batch->effectiveRewardPoints() }}</dd></div>
                </dl>
            </div>

            @can('qr-codes.generate')
                <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold mb-1">Generate QR codes</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ number_format($stats['remaining']) }} of {{ number_format($batch->quantity) }} remaining to generate.</p>
                    <form method="POST" action="{{ route('batches.generate-qr', $batch) }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Quantity</label>
                            <input type="number" min="1" name="quantity" value="{{ min(300, max(1, $stats['remaining'])) }}" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5 w-40">
                        </div>
                        <button class="rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium px-5 py-2.5" @disabled($stats['remaining'] <= 0)>Generate</button>
                        @can('qr-codes.print')
                            <a href="{{ route('qr-codes.print', $batch) }}" target="_blank" class="rounded-lg border border-slate-300 dark:border-slate-700 px-5 py-2.5 font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Print sheet</a>
                        @endcan
                    </form>
                </div>
            @endcan
        </div>

        <div class="grid grid-cols-3 lg:grid-cols-1 gap-3 content-start">
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 text-center">
                <div class="text-2xl font-bold">{{ number_format($stats['generated']) }}</div><div class="text-xs text-slate-400">Generated</div>
            </div>
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 text-center">
                <div class="text-2xl font-bold text-amber-600">{{ number_format($stats['remaining']) }}</div><div class="text-xs text-slate-400">Remaining</div>
            </div>
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 text-center">
                <div class="text-2xl font-bold text-emerald-600">{{ number_format($stats['verified']) }}</div><div class="text-xs text-slate-400">Verified</div>
            </div>
        </div>
    </div>
@endsection
