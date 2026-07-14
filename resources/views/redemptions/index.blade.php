@extends('layouts.app')
@section('title', 'Redemptions')

@section('content')
    <div class="grid sm:grid-cols-2 gap-4 mb-6 max-w-md">
        <div class="lux-card p-4">
            <div class="text-sm text-slate-400">Pending</div><div class="text-2xl font-bold text-amber-600">{{ number_format($counts['pending']) }}</div>
        </div>
        <div class="lux-card p-4">
            <div class="text-sm text-slate-400">Approved</div><div class="text-2xl font-bold text-emerald-600">{{ number_format($counts['approved']) }}</div>
        </div>
    </div>

    <form method="GET" class="mb-4 grid sm:grid-cols-3 gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search reference / user…" class="lux-field px-3 py-2 text-sm">
        <select name="status" class="lux-field px-3 py-2 text-sm">
            <option value="">Any status</option>
            @foreach (['pending','approved','rejected'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? null) === $s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
        <button class="lux-field px-3 py-2 text-sm font-medium">Filter</button>
    </form>

    <div class="lux-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-transparent text-left text-slate-500 dark:text-slate-400">
                    <tr><th class="px-4 py-3 font-medium">Reference</th><th class="px-4 py-3 font-medium">User</th><th class="px-4 py-3 font-medium">Amount</th><th class="px-4 py-3 font-medium">Method</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($requests as $req)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-mono text-xs">{{ $req->reference }}</td>
                            <td class="px-4 py-3">{{ $req->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-semibold">₹{{ number_format((float) $req->amount, 2) }}</td>
                            <td class="px-4 py-3 uppercase text-xs">{{ $req->method }}</td>
                            <td class="px-4 py-3"><x-badge :status="$req->status" /></td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('redemptions.show', $req) }}" class="text-brand-600 hover:underline">Review</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No redemption requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $requests->links() }}</div>
@endsection
