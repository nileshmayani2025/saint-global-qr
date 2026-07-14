@extends('layouts.app')
@section('title', 'My Scans')

@section('content')
    <div class="grid sm:grid-cols-2 gap-4 mb-6 max-w-md">
        <div class="lux-card p-4">
            <div class="text-sm text-slate-400">Total scans</div><div class="text-2xl font-bold">{{ number_format($summary['total_scans']) }}</div>
        </div>
        <div class="lux-card p-4">
            <div class="text-sm text-slate-400">Points earned</div><div class="text-2xl font-bold text-amber-600">{{ number_format($summary['points_earned']) }}</div>
        </div>
    </div>

    <div class="lux-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-transparent text-left text-slate-500 dark:text-slate-400">
                    <tr><th class="px-4 py-3 font-medium">Product</th><th class="px-4 py-3 font-medium">Code</th><th class="px-4 py-3 font-medium">Points</th><th class="px-4 py-3 font-medium">Verified</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($verifications as $v)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $v->product?->name ?? '—' }}<div class="text-xs text-slate-400">{{ $v->product?->sku }}</div></td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $v->qrCode?->code }}</td>
                            <td class="px-4 py-3 text-amber-600 font-semibold">+{{ $v->reward_points }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ optional($v->verified_at)->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">You haven't verified any products yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $verifications->links() }}</div>
@endsection
