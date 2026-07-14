@extends('layouts.app')
@section('title', 'QR Code')

@section('content')
    <a href="{{ route('qr-codes.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to QR codes</a>

    <div class="mt-4 grid lg:grid-cols-3 gap-6">
        <div class="lux-card p-6 text-center">
            @if ($qrCode->image_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($qrCode->image_path) }}" class="w-full max-w-[240px] mx-auto aspect-square object-contain bg-white p-3 rounded-lg border border-slate-100">
            @endif
            <div class="mt-4 font-mono text-sm break-all">{{ $qrCode->code }}</div>
            <div class="mt-2"><x-badge :status="$qrCode->status" /></div>
            @can('qr-codes.block')
                @unless ($qrCode->isBlocked())
                    <form method="POST" action="{{ route('qr-codes.block', $qrCode) }}" class="mt-4" onsubmit="return confirm('Block this QR code?')">
                        @csrf<button class="w-full rounded-lg border border-rose-300 text-rose-600 font-medium px-4 py-2 hover:bg-rose-50 dark:hover:bg-rose-900/20">Block QR code</button>
                    </form>
                @endunless
            @endcan
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="lux-card p-6">
                <h3 class="font-semibold mb-4">Details</h3>
                <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-slate-400">Product</dt><dd class="font-medium">{{ $qrCode->product?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">Brand</dt><dd class="font-medium">{{ $qrCode->product?->brand?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">Batch</dt><dd class="font-medium">{{ $qrCode->batch?->code ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">Serial</dt><dd class="font-medium">#{{ $qrCode->serial }}</dd></div>
                    <div><dt class="text-slate-400">Reward points</dt><dd class="font-medium">{{ $qrCode->reward_points }}</dd></div>
                    <div><dt class="text-slate-400">Scans</dt><dd class="font-medium">{{ $qrCode->scan_count }}</dd></div>
                    <div><dt class="text-slate-400">Verified at</dt><dd class="font-medium">{{ optional($qrCode->verified_at)->format('d M Y, H:i') ?? '—' }}</dd></div>
                </dl>
            </div>

            <div class="lux-card p-6">
                <h3 class="font-semibold mb-4">Recent scans</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-slate-400"><tr><th class="py-2 font-medium">Result</th><th class="py-2 font-medium">Device</th><th class="py-2 font-medium">Fraud</th><th class="py-2 font-medium">When</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($scans as $scan)
                                <tr>
                                    <td class="py-2"><x-badge :status="$scan->result" /></td>
                                    <td class="py-2 text-slate-500">{{ $scan->device ?? $scan->browser ?? '—' }}</td>
                                    <td class="py-2">@if ($scan->is_fraud_suspected)<span class="text-rose-600 text-xs font-medium">Suspected</span>@else<span class="text-slate-400 text-xs">—</span>@endif</td>
                                    <td class="py-2 text-slate-500">{{ $scan->created_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center text-slate-400">No scans yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
