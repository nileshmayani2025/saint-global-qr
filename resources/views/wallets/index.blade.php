@extends('layouts.app')
@section('title', 'Wallets')

@section('content')
    <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
            <div class="text-sm text-slate-400">Wallets</div><div class="text-2xl font-bold">{{ number_format($totals['wallets']) }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
            <div class="text-sm text-slate-400">Total balance</div><div class="text-2xl font-bold text-emerald-600">₹{{ number_format((float) $totals['balance'], 2) }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
            <div class="text-sm text-slate-400">Lifetime credited</div><div class="text-2xl font-bold">₹{{ number_format((float) $totals['credited'], 2) }}</div>
        </div>
    </div>

    <form method="GET" class="mb-4 grid sm:grid-cols-3 gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search user…" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
        <select name="type" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
            <option value="">All types</option>
            <option value="reward" @selected(($filters['type'] ?? null) === 'reward')>Reward</option>
            <option value="cashback" @selected(($filters['type'] ?? null) === 'cashback')>Cashback</option>
        </select>
        <button class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-medium">Filter</button>
    </form>

    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-left text-slate-500 dark:text-slate-400">
                    <tr><th class="px-4 py-3 font-medium">User</th><th class="px-4 py-3 font-medium">Type</th><th class="px-4 py-3 font-medium">Balance</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($wallets as $wallet)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3"><div class="font-medium">{{ $wallet->user?->name ?? '—' }}</div><div class="text-xs text-slate-400">{{ $wallet->user?->email }}</div></td>
                            <td class="px-4 py-3 capitalize">{{ $wallet->type }}</td>
                            <td class="px-4 py-3 font-semibold">₹{{ number_format((float) $wallet->balance, 2) }}</td>
                            <td class="px-4 py-3"><x-badge :status="$wallet->status" /></td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('wallets.show', $wallet) }}" class="text-brand-600 hover:underline">View ledger</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No wallets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $wallets->links() }}</div>
@endsection
