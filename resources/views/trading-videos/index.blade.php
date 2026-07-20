@extends('layouts.app')
@section('title', 'Trading Videos')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $videos->total() }} video(s)</p>
        @can('trading-videos.create')
            <a href="{{ route('trading-videos.create') }}" class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">+ New video</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search videos…" class="lux-field px-3 py-2 text-sm flex-1 max-w-xs">
        <select name="product_id" class="lux-field px-3 py-2 text-sm max-w-xs">
            <option value="">All products</option>
            @foreach ($products as $p)
                <option value="{{ $p->id }}" @selected(($filters['product_id'] ?? null) == $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
        <button class="lux-ghost px-4 py-2 text-sm">Filter</button>
    </form>

    <div class="lux-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-transparent text-left text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Product</th>
                        <th class="px-4 py-3 font-medium">Link</th>
                        <th class="px-4 py-3 font-medium">Sort</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($videos as $video)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-medium">{{ $video->displayTitle() }}</td>
                            <td class="px-4 py-3 text-slate-500">
                                {{ $video->product?->name ?? '—' }}
                                <div class="text-xs text-slate-400">{{ $video->product?->sku }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ $video->url }}" target="_blank" rel="noopener"
                                   class="text-brand-500 hover:underline break-all line-clamp-1">{{ $video->url }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $video->sort_order }}</td>
                            <td class="px-4 py-3"><x-badge :status="$video->status" /></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('trading-videos.update')<x-act.edit :href="route('trading-videos.edit', $video)" />@endcan
                                    @can('trading-videos.delete')<x-act.delete :action="route('trading-videos.destroy', $video)" confirm="Delete this video?" />@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No trading videos yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $videos->withQueryString()->links() }}</div>
@endsection
