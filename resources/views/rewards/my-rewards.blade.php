@extends('layouts.app')
@section('title', 'My Rewards')

@section('content')
    <div class="grid lg:grid-cols-3 gap-6" x-data="{ method: '{{ old('method', 'upi') }}' }">
        <div class="space-y-6">
            <div class="rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-white p-6">
                <div class="text-sm text-white/70">Available balance</div>
                <div class="mt-1 text-3xl font-bold">₹{{ number_format((float) $available, 2) }}</div>
                <div class="mt-4 pt-4 border-t border-white/20 text-sm flex justify-between">
                    <span class="text-white/70">Wallet balance</span><span>₹{{ number_format((float) $wallet->balance, 2) }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('my.rewards.payout') }}" class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 space-y-4">
                @csrf
                <h3 class="font-semibold">Request payout</h3>
                <div>
                    <label class="block text-sm mb-1">Amount (₹)</label>
                    <input type="number" step="0.01" min="1" name="amount" value="{{ old('amount') }}" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Method</label>
                    <select name="method" x-model="method" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                        <option value="upi">UPI</option><option value="bank">Bank transfer</option><option value="gift">Gift</option><option value="cash">Cash</option>
                    </select>
                </div>
                <div x-show="method === 'upi'">
                    <label class="block text-sm mb-1">UPI ID</label>
                    <input name="upi_id" value="{{ old('upi_id') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                </div>
                <div x-show="method === 'bank'" x-cloak class="space-y-3">
                    <input name="account_name" value="{{ old('account_name') }}" placeholder="Account holder name" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                    <input name="account_number" value="{{ old('account_number') }}" placeholder="Account number" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                    <input name="ifsc" value="{{ old('ifsc') }}" placeholder="IFSC code" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                </div>
                <textarea name="note" rows="2" placeholder="Note (optional)" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">{{ old('note') }}</textarea>
                <button class="w-full rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium py-2.5">Submit request</button>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
            <h3 class="font-semibold p-5 pb-3">My redemption requests</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-left text-slate-500 dark:text-slate-400">
                        <tr><th class="px-4 py-3 font-medium">Reference</th><th class="px-4 py-3 font-medium">Amount</th><th class="px-4 py-3 font-medium">Method</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium">Date</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($requests as $req)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ $req->reference }}</td>
                                <td class="px-4 py-3 font-semibold">₹{{ number_format((float) $req->amount, 2) }}</td>
                                <td class="px-4 py-3 uppercase text-xs">{{ $req->method }}</td>
                                <td class="px-4 py-3"><x-badge :status="$req->status" /></td>
                                <td class="px-4 py-3 text-slate-500">{{ $req->created_at?->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No requests yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $requests->links() }}</div>
        </div>
    </div>
@endsection
