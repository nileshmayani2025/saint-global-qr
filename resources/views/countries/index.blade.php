@extends('layouts.app')
@section('title', 'Countries')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $countries->total() }} country(ies)</p>
        @can('countries.create')
            <a href="{{ route('countries.create') }}" class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">+ New country</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search countries…" class="lux-field px-3 py-2 text-sm flex-1 max-w-xs">
        <button class="lux-ghost px-4 py-2 text-sm">Filter</button>
    </form>

    <div class="lux-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-transparent text-left text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">ISO2</th>
                        <th class="px-4 py-3 font-medium">ISO3</th>
                        <th class="px-4 py-3 font-medium">Phone code</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($countries as $country)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-medium">{{ $country->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $country->iso2 ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $country->iso3 ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $country->phone_code ?? '—' }}</td>
                            <td class="px-4 py-3"><x-badge :status="$country->status" /></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('countries.update')<x-act.edit :href="route('countries.edit', $country)" />@endcan
                                    @can('countries.delete')<x-act.delete :action="route('countries.destroy', $country)" confirm="Delete this country? Its states and cities will be removed too." />@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No countries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $countries->withQueryString()->links() }}</div>
@endsection
