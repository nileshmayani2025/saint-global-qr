@extends('layouts.app')
@section('title', 'States')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $states->total() }} state(s)</p>
        @can('states.create')
            <a href="{{ route('states.create') }}" class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">+ New state</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search states…" class="lux-field px-3 py-2 text-sm flex-1 max-w-xs">
        <select name="country_id" class="lux-field px-3 py-2 text-sm max-w-xs">
            <option value="">All countries</option>
            @foreach ($countries as $c)
                <option value="{{ $c->id }}" @selected(($filters['country_id'] ?? null) == $c->id)>{{ $c->name }}</option>
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
                        <th class="px-4 py-3 font-medium">Country</th>
                        <th class="px-4 py-3 font-medium">Code</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($states as $state)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-medium">{{ $state->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $state->country?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $state->code ?? '—' }}</td>
                            <td class="px-4 py-3"><x-badge :status="$state->status" /></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('states.update')<x-act.edit :href="route('states.edit', $state)" />@endcan
                                    @can('states.delete')<x-act.delete :action="route('states.destroy', $state)" confirm="Delete this state? Its cities will be removed too." />@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No states found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $states->withQueryString()->links() }}</div>
@endsection
