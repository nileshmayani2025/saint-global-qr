@extends('layouts.app')
@section('title', 'Leads')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">
            {{ $leads->total() }} lead(s)
            @unless ($canSeeAll) <span class="text-slate-400">· showing only leads you captured</span> @endunless
        </p>
        @can('leads.create')
            <a href="{{ route('leads.create') }}" class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">+ New lead</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, mobile, remark…" class="lux-field px-3 py-2 text-sm flex-1 max-w-xs">
        <select name="status" class="lux-field px-3 py-2 text-sm max-w-[11rem]">
            <option value="">All statuses</option>
            @foreach ($statuses as $s)
                <option value="{{ $s }}" @selected(($filters['status'] ?? null) === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="lux-ghost px-4 py-2 text-sm">Filter</button>
    </form>

    <div class="lux-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-transparent text-left text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Mobile</th>
                        <th class="px-4 py-3 font-medium">Location</th>
                        <th class="px-4 py-3 font-medium">Remark</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        @if ($canSeeAll)<th class="px-4 py-3 font-medium">Added by</th>@endif
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($leads as $lead)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-medium">{{ $lead->name }}</td>
                            <td class="px-4 py-3">
                                <a href="tel:{{ $lead->phone }}" class="text-brand-500 hover:underline">{{ $lead->phone }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-500">
                                {{ collect([$lead->city?->name, $lead->state?->name])->filter()->implode(', ') ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-slate-500 max-w-xs"><span class="line-clamp-1">{{ $lead->remark ?: '—' }}</span></td>
                            <td class="px-4 py-3"><x-badge :status="$lead->status" /></td>
                            @if ($canSeeAll)
                                <td class="px-4 py-3 text-slate-500">{{ $lead->createdBy?->name ?? '—' }}</td>
                            @endif
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <x-act.view :href="route('leads.show', $lead)" />
                                    @can('update', $lead)<x-act.edit :href="route('leads.edit', $lead)" />@endcan
                                    @can('delete', $lead)<x-act.delete :action="route('leads.destroy', $lead)" confirm="Delete this lead?" />@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canSeeAll ? 7 : 6 }}" class="px-4 py-10 text-center text-slate-400">No leads yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $leads->withQueryString()->links() }}</div>
@endsection
