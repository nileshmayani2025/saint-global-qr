@extends('layouts.app')
@section('title', 'Banners')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $banners->total() }} banner(s) · shown on the app home carousel</p>
        @can('banners.create')
            <a href="{{ route('banners.create') }}" class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">+ New banner</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search banners…" class="lux-field px-3 py-2 text-sm flex-1 max-w-xs">
        <button class="lux-ghost px-4 py-2 text-sm">Filter</button>
    </form>

    <div class="lux-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-transparent text-left text-slate-500 dark:text-slate-400">
                    <tr><th class="px-4 py-3 font-medium">Banner</th><th class="px-4 py-3 font-medium">Link</th><th class="px-4 py-3 font-medium">Order</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($banners as $banner)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($banner->image_path)
                                        <img src="{{ asset('media/'.$banner->image_path) }}" alt="" class="w-16 h-10 object-cover rounded-lg border border-slate-200 dark:border-slate-700 shrink-0">
                                    @else
                                        <div class="w-16 h-10 rounded-lg grid place-items-center bg-slate-100 dark:bg-slate-800 text-slate-400 text-[10px] shrink-0">No image</div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-medium truncate">{{ $banner->title }}</div>
                                        <div class="text-xs text-slate-400 truncate">{{ $banner->subtitle ?: '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $banner->link_url ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $banner->sort_order }}</td>
                            <td class="px-4 py-3"><x-badge :status="$banner->status" /></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('banners.update')<x-act.edit :href="route('banners.edit', $banner)" />@endcan
                                    @can('banners.delete')<x-act.delete :action="route('banners.destroy', $banner)" confirm="Delete this banner?" />@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No banners yet. Create one to fill the app home carousel.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $banners->withQueryString()->links() }}</div>
@endsection
