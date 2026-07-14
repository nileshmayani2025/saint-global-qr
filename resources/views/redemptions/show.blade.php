@extends('layouts.app')
@section('title', 'Redemption ' . $redemption->reference)

@section('content')
    <a href="{{ route('redemptions.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to redemptions</a>

    <div class="mt-4 grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 lux-card p-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="font-mono text-xs text-slate-400">{{ $redemption->reference }}</div>
                    <div class="text-2xl font-bold mt-1">₹{{ number_format((float) $redemption->amount, 2) }}</div>
                </div>
                <x-badge :status="$redemption->status" />
            </div>
            <dl class="mt-6 grid sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-400">Requested by</dt><dd class="font-medium">{{ $redemption->user?->name }}</dd></div>
                <div><dt class="text-slate-400">Contact</dt><dd class="font-medium">{{ $redemption->user?->phone ?? $redemption->user?->email }}</dd></div>
                <div><dt class="text-slate-400">Method</dt><dd class="font-medium uppercase">{{ $redemption->method }}</dd></div>
                <div><dt class="text-slate-400">Requested at</dt><dd class="font-medium">{{ $redemption->created_at?->format('d M Y, H:i') }}</dd></div>
                @foreach ((array) $redemption->payout_details as $k => $v)
                    <div><dt class="text-slate-400 capitalize">{{ str_replace('_', ' ', $k) }}</dt><dd class="font-medium">{{ $v }}</dd></div>
                @endforeach
                @if ($redemption->note)<div class="sm:col-span-2"><dt class="text-slate-400">Note</dt><dd class="font-medium">{{ $redemption->note }}</dd></div>@endif
            </dl>

            @if ($redemption->status !== 'pending')
                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 text-sm space-y-2">
                    <div class="flex justify-between"><span class="text-slate-400">Reviewed by</span><span class="font-medium">{{ $redemption->reviewer?->name ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Reviewed at</span><span class="font-medium">{{ optional($redemption->reviewed_at)->format('d M Y, H:i') ?? '—' }}</span></div>
                    @if ($redemption->rejection_reason)<div><span class="text-slate-400">Rejection reason:</span> {{ $redemption->rejection_reason }}</div>@endif
                    @if ($redemption->review_note)<div><span class="text-slate-400">Review note:</span> {{ $redemption->review_note }}</div>@endif
                </div>
            @endif
        </div>

        @if ($redemption->isPending())
            <div class="space-y-4">
                @can('redemptions.approve')
                    <form method="POST" action="{{ route('redemptions.approve', $redemption) }}" enctype="multipart/form-data" class="lux-card p-5 space-y-3">
                        @csrf
                        <h3 class="font-semibold text-emerald-600">Approve</h3>
                        <div>
                            <label class="block text-sm mb-1">Payout proof (optional)</label>
                            <input type="file" name="attachment" accept="image/*,application/pdf" class="w-full text-sm">
                        </div>
                        <textarea name="review_note" rows="2" placeholder="Review note (optional)" class="w-full lux-field px-3 py-2 text-sm"></textarea>
                        <button class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5" onsubmit="return confirm('Approve and debit points?')">Approve &amp; debit</button>
                    </form>
                @endcan
                @can('redemptions.reject')
                    <form method="POST" action="{{ route('redemptions.reject', $redemption) }}" class="lux-card p-5 space-y-3">
                        @csrf
                        <h3 class="font-semibold text-rose-600">Reject</h3>
                        <textarea name="rejection_reason" rows="2" required placeholder="Reason for rejection" class="w-full lux-field px-3 py-2 text-sm"></textarea>
                        <button class="w-full rounded-lg border border-rose-300 text-rose-600 font-medium py-2.5 hover:bg-rose-50 dark:hover:bg-rose-900/20">Reject request</button>
                    </form>
                @endcan
            </div>
        @endif
    </div>
@endsection
