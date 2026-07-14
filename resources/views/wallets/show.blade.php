@extends('layouts.app')
@section('title', 'Wallet ledger')

@section('content')
    <a href="{{ route('wallets.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to wallets</a>

    <div class="mt-4 grid lg:grid-cols-3 gap-6">
        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6">
            <div class="text-sm text-slate-400">{{ ucfirst($wallet->type) }} wallet</div>
            <div class="mt-1 text-3xl font-bold text-emerald-600">₹{{ number_format((float) $wallet->balance, 2) }}</div>
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-400">Owner</span><span class="font-medium">{{ $wallet->user?->name }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Lifetime credited</span><span class="font-medium">₹{{ number_format((float) $wallet->lifetime_credited, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Lifetime debited</span><span class="font-medium">₹{{ number_format((float) $wallet->lifetime_debited, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Credits / Debits</span><span class="font-medium">{{ $summary['credits'] }} / {{ $summary['debits'] }}</span></div>
            </div>
        </div>

        <div class="lg:col-span-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
            <h3 class="font-semibold p-5 pb-3">Transactions</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-left text-slate-500 dark:text-slate-400">
                        <tr><th class="px-4 py-3 font-medium">Reason</th><th class="px-4 py-3 font-medium">Amount</th><th class="px-4 py-3 font-medium">Balance</th><th class="px-4 py-3 font-medium">When</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($transactions as $txn)
                            <tr>
                                <td class="px-4 py-3"><div class="capitalize">{{ str_replace('_', ' ', $txn->reason) }}</div><div class="text-xs text-slate-400">{{ $txn->description }}</div></td>
                                <td class="px-4 py-3 font-semibold {{ $txn->isCredit() ? 'text-emerald-600' : 'text-rose-600' }}">{{ $txn->isCredit() ? '+' : '−' }}₹{{ number_format((float) $txn->amount, 2) }}</td>
                                <td class="px-4 py-3">₹{{ number_format((float) $txn->balance_after, 2) }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $txn->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No transactions.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $transactions->links() }}</div>
        </div>
    </div>
@endsection
