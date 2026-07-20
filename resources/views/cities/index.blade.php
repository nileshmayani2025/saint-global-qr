@extends('layouts.app')
@section('title', 'Cities')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $cities->total() }} city(ies)</p>
        @can('cities.create')
            <a href="{{ route('cities.create') }}" class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">+ New city</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search cities…" class="lux-field px-3 py-2 text-sm flex-1 max-w-xs">
        <select name="state_id" class="lux-field px-3 py-2 text-sm max-w-xs">
            <option value="">All states</option>
            @foreach ($states as $s)
                <option value="{{ $s->id }}" @selected(($filters['state_id'] ?? null) == $s->id)>{{ $s->name }}</option>
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
                        <th class="px-4 py-3 font-medium">State</th>
                        <th class="px-4 py-3 font-medium">Country</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($cities as $city)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-medium">{{ $city->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $city->state?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $city->state?->country?->name ?? '—' }}</td>
                            <td class="px-4 py-3"><x-badge :status="$city->status" /></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('cities.update')<x-act.edit :href="route('cities.edit', $city)" />@endcan
                                    @can('cities.delete')<x-act.delete :action="route('cities.destroy', $city)" confirm="Delete this city?" />@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No cities found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $cities->withQueryString()->links() }}</div>
@endsection
