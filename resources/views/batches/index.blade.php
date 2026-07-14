@extends('layouts.app')
@section('title', 'Batches')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $batches->total() }} batch(es)</p>
        @can('batches.create')
            <a href="{{ route('batches.create') }}" class="rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium px-4 py-2">+ New batch</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 grid sm:grid-cols-4 gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search code…" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
        <select name="product_id" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
            <option value="">All products</option>
            @foreach ($products as $p)<option value="{{ $p->id }}" @selected(($filters['product_id'] ?? null) == $p->id)>{{ $p->name }}</option>@endforeach
        </select>
        <select name="status" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
            <option value="">Any status</option>
            @foreach (['draft','generating','active','closed'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? null) === $s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
        <button class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-medium">Filter</button>
    </form>

    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-left text-slate-500 dark:text-slate-400">
                    <tr><th class="px-4 py-3 font-medium">Code</th><th class="px-4 py-3 font-medium">Product</th><th class="px-4 py-3 font-medium">Qty</th><th class="px-4 py-3 font-medium">Generated</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($batches as $batch)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-medium"><a href="{{ route('batches.show', $batch) }}" class="hover:text-brand-600">{{ $batch->code }}</a></td>
                            <td class="px-4 py-3">{{ $batch->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ number_format($batch->quantity) }}</td>
                            <td class="px-4 py-3">{{ number_format($batch->qr_generated) }}</td>
                            <td class="px-4 py-3"><x-badge :status="$batch->status" /></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('batches.show', $batch) }}" class="text-brand-600 hover:underline">View</a>
                                @can('batches.update')<a href="{{ route('batches.edit', $batch) }}" class="ml-3 text-slate-500 hover:underline">Edit</a>@endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No batches found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $batches->withQueryString()->links() }}</div>
@endsection
