@extends('layouts.app')
@section('title', $product->name)

@section('content')
    <a href="{{ route('products.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to products</a>

    <div class="mt-4 grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6">
            <div class="flex items-start gap-4">
                @if ($product->image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" class="w-24 h-24 object-cover rounded-lg border border-slate-200 dark:border-slate-700">
                @endif
                <div>
                    <h2 class="text-xl font-bold">{{ $product->name }}</h2>
                    <p class="text-slate-500">{{ $product->sku }} · <x-badge :status="$product->status" /></p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $product->description }}</p>
                </div>
            </div>
            <dl class="mt-6 grid sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-400">Brand</dt><dd class="font-medium">{{ $product->brand?->name ?? '—' }}</dd></div>
                <div><dt class="text-slate-400">Category</dt><dd class="font-medium">{{ $product->category?->name ?? '—' }}</dd></div>
                <div><dt class="text-slate-400">MRP</dt><dd class="font-medium">₹{{ number_format((float) $product->mrp, 2) }}</dd></div>
                <div><dt class="text-slate-400">Reward points</dt><dd class="font-medium">{{ $product->reward_points }}</dd></div>
                <div><dt class="text-slate-400">Unit</dt><dd class="font-medium">{{ $product->unit }}</dd></div>
                <div><dt class="text-slate-400">HSN code</dt><dd class="font-medium">{{ $product->hsn_code ?? '—' }}</dd></div>
            </dl>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 text-center">
                    <div class="text-2xl font-bold">{{ $stats['batches'] }}</div><div class="text-xs text-slate-400">Batches</div>
                </div>
                <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 text-center">
                    <div class="text-2xl font-bold">{{ $stats['qr_codes'] }}</div><div class="text-xs text-slate-400">QR codes</div>
                </div>
                <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 text-center">
                    <div class="text-2xl font-bold text-emerald-600">{{ $stats['verified'] }}</div><div class="text-xs text-slate-400">Verified</div>
                </div>
            </div>
            @can('products.update')
                <a href="{{ route('products.edit', $product) }}" class="block text-center rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium px-4 py-2.5">Edit product</a>
            @endcan

            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="font-semibold mb-3 text-sm">Recent batches</h3>
                <div class="space-y-2 text-sm">
                    @forelse ($product->batches as $batch)
                        <a href="{{ route('batches.show', $batch) }}" class="flex justify-between hover:text-brand-600">
                            <span>{{ $batch->code }}</span><x-badge :status="$batch->status" />
                        </a>
                    @empty
                        <p class="text-slate-400">No batches yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
