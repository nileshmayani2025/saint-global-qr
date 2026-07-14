@extends('layouts.app')
@section('title', 'Products')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $products->total() }} product(s)</p>
        @can('products.create')
            <a href="{{ route('products.create') }}" class="rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium px-4 py-2">+ New product</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 grid sm:grid-cols-4 gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name / SKU…" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
        <select name="brand_id" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
            <option value="">All brands</option>
            @foreach ($brands as $b)<option value="{{ $b->id }}" @selected(($filters['brand_id'] ?? null) == $b->id)>{{ $b->name }}</option>@endforeach
        </select>
        <select name="status" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
            <option value="">Any status</option>
            <option value="active" @selected(($filters['status'] ?? null) === 'active')>Active</option>
            <option value="inactive" @selected(($filters['status'] ?? null) === 'inactive')>Inactive</option>
        </select>
        <button class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Filter</button>
    </form>

    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-left text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Product</th>
                        <th class="px-4 py-3 font-medium">SKU</th>
                        <th class="px-4 py-3 font-medium">Brand</th>
                        <th class="px-4 py-3 font-medium">MRP</th>
                        <th class="px-4 py-3 font-medium">Points</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('products.show', $product) }}" class="hover:text-brand-600">{{ $product->name }}</a>
                                <div class="text-xs text-slate-400">{{ $product->category?->name }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $product->sku }}</td>
                            <td class="px-4 py-3">{{ $product->brand?->name ?? '—' }}</td>
                            <td class="px-4 py-3">₹{{ number_format((float) $product->mrp, 2) }}</td>
                            <td class="px-4 py-3">{{ $product->reward_points }}</td>
                            <td class="px-4 py-3"><x-badge :status="$product->status" /></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @can('products.update')<a href="{{ route('products.edit', $product) }}" class="text-brand-600 hover:underline">Edit</a>@endcan
                                @can('products.delete')
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline" onsubmit="return confirm('Delete this product?')">
                                        @csrf @method('DELETE')<button class="ml-3 text-rose-600 hover:underline">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $products->withQueryString()->links() }}</div>
@endsection
