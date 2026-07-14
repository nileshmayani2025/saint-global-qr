@extends('layouts.app')
@section('title', 'Brands')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $brands->total() }} brand(s)</p>
        @can('brands.create')
            <a href="{{ route('brands.create') }}" class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">+ New brand</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search brands…" class="lux-field px-3 py-2 text-sm flex-1 max-w-xs">
        <button class="lux-ghost px-4 py-2 text-sm">Filter</button>
    </form>

    <div class="lux-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-transparent text-left text-slate-500 dark:text-slate-400">
                    <tr><th class="px-4 py-3 font-medium">Name</th><th class="px-4 py-3 font-medium">Sort</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($brands as $brand)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-medium">{{ $brand->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $brand->sort_order }}</td>
                            <td class="px-4 py-3"><x-badge :status="$brand->status" /></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @can('brands.update')<a href="{{ route('brands.edit', $brand) }}" class="text-brand-600 hover:underline">Edit</a>@endcan
                                @can('brands.delete')
                                    <form method="POST" action="{{ route('brands.destroy', $brand) }}" class="inline" onsubmit="return confirm('Delete this brand?')">@csrf @method('DELETE')<button class="ml-3 text-rose-600 hover:underline">Delete</button></form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No brands found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $brands->withQueryString()->links() }}</div>
@endsection
