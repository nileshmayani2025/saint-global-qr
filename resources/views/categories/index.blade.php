@extends('layouts.app')
@section('title', 'Categories')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $categories->total() }} categor(ies)</p>
        @can('categories.create')
            <a href="{{ route('categories.create') }}" class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">+ New category</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search categories…" class="lux-field px-3 py-2 text-sm flex-1 max-w-xs">
        <button class="lux-ghost px-4 py-2 text-sm">Filter</button>
    </form>

    <div class="lux-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-transparent text-left text-slate-500 dark:text-slate-400">
                    <tr><th class="px-4 py-3 font-medium">Name</th><th class="px-4 py-3 font-medium">Parent</th><th class="px-4 py-3 font-medium">Sort</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $category->parent?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $category->sort_order }}</td>
                            <td class="px-4 py-3"><x-badge :status="$category->status" /></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @can('categories.update')<a href="{{ route('categories.edit', $category) }}" class="text-brand-600 hover:underline">Edit</a>@endcan
                                @can('categories.delete')
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')<button class="ml-3 text-rose-600 hover:underline">Delete</button></form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $categories->withQueryString()->links() }}</div>
@endsection
