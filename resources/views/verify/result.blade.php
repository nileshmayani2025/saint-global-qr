@php
    $rewardPoints = (int) ($result->verification?->reward_points ?? 0);

    // A signed-in user who just earned points gets the celebration; everyone
    // else — guests verifying a pack, duplicate scans, failures — gets the
    // plain result card below.
    $celebrate = auth()->check() && $result->genuine && $result->isValid() && $rewardPoints > 0;
@endphp

{{-- Signed-in scans come from the app, so they keep the app shell and its
     bottom nav instead of dropping into the bare guest page. --}}
@extends(auth()->check() ? 'layouts.consumer' : 'layouts.guest')
@section('title', 'Verification result')

@section('content')
    @if ($celebrate)
        @include('partials.scan-reward', [
            'points' => $rewardPoints,
            'productName' => $result->qrCode?->product?->name,
        ])
    @else
    @php
        $genuine = $result->genuine && $result->isValid();
        $duplicate = $result->isDuplicate();
        [$tone, $icon, $heading] = $genuine
            ? ['emerald', 'M9 12l2 2 4-4', 'Genuine product']
            : ($duplicate
                ? ['amber', 'M12 9v2m0 4h.01M12 3l9 16H3z', 'Already verified']
                : ['rose', 'M6 18L18 6M6 6l12 12', 'Verification failed']);
        $product = $result->qrCode?->product;
    @endphp

    <div class="text-center">
        <div class="w-16 h-16 mx-auto rounded-full grid place-items-center bg-{{ $tone }}-100 text-{{ $tone }}-600 dark:bg-{{ $tone }}-900/40 dark:text-{{ $tone }}-400">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
        </div>
        <h1 class="mt-4 text-2xl font-bold text-{{ $tone }}-600 dark:text-{{ $tone }}-400">{{ $heading }}</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $result->message }}</p>
    </div>

    @if ($product)
        <div class="mt-8 lux-card p-6">
            <h2 class="font-semibold text-lg">{{ $product->name }}</h2>
            <p class="text-sm text-slate-500">{{ $product->brand?->name }}</p>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-400">SKU</dt><dd class="font-medium">{{ $product->sku }}</dd></div>
                @if ($result->qrCode?->batch)
                    <div class="flex justify-between"><dt class="text-slate-400">Batch</dt><dd class="font-medium">{{ $result->qrCode->batch->code }}</dd></div>
                    @if ($result->qrCode->batch->expiry_date)
                        <div class="flex justify-between"><dt class="text-slate-400">Expiry</dt><dd class="font-medium">{{ $result->qrCode->batch->expiry_date->format('d M Y') }}</dd></div>
                    @endif
                @endif
                @if ($result->verification?->reward_points)
                    <div class="flex justify-between"><dt class="text-slate-400">Reward points</dt><dd class="font-medium text-amber-600">+{{ $result->verification->reward_points }}</dd></div>
                @endif
            </dl>
        </div>
    @endif

    @if (! empty($result->reasons))
        <div class="mt-4 rounded-xl border border-{{ $tone }}-200 bg-{{ $tone }}-50 dark:border-{{ $tone }}-900/50 dark:bg-{{ $tone }}-900/20 p-4 text-sm text-{{ $tone }}-800 dark:text-{{ $tone }}-200">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($result->reasons as $reason)<li>{{ $reason }}</li>@endforeach
            </ul>
        </div>
    @endif

    <a href="{{ route('verify.form') }}" class="mt-6 block text-center rounded-lg border border-slate-300 dark:border-slate-700 px-4 py-3 font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Verify another product</a>
    @endif
@endsection
