@extends('layouts.app')
@section('title', 'Users')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $users->total() }} user(s)</p>
        @can('users.create')
            <a href="{{ route('users.create') }}" class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">+ New user</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 grid sm:grid-cols-4 gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name / email…" class="lux-field px-3 py-2 text-sm">
        <select name="role" class="lux-field px-3 py-2 text-sm">
            <option value="">All roles</option>
            @foreach ($roles as $r)<option value="{{ $r }}" @selected(($filters['role'] ?? null) === $r)>{{ ucwords(str_replace('-', ' ', $r)) }}</option>@endforeach
        </select>
        <select name="status" class="lux-field px-3 py-2 text-sm">
            <option value="">Any status</option>
            @foreach (['active','inactive','suspended'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? null) === $s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
        <button class="lux-ghost px-4 py-2 text-sm">Filter</button>
    </form>

    <div class="lux-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-transparent text-left text-slate-500 dark:text-slate-400">
                    <tr><th class="px-4 py-3 font-medium">User</th><th class="px-4 py-3 font-medium">Roles</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium">Approval</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3">
                                <a href="{{ route('users.show', $user) }}" class="font-medium hover:text-brand-600">{{ $user->name }}</a>
                                <div class="text-xs text-slate-400">{{ $user->email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @foreach ($user->getRoleNames() as $role)
                                    <span class="inline-block text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 mr-1">{{ $role }}</span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3"><x-badge :status="$user->status" /></td>
                            <td class="px-4 py-3">
                                @if ($user->isApproved())
                                    <span class="text-emerald-600 text-xs font-medium">Approved</span>
                                @else
                                    <span class="text-amber-600 text-xs font-medium">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('users.update')
                                        @if ($user->isApproved())
                                            <form method="POST" action="{{ route('users.revoke-approval', $user) }}" class="inline-flex">@csrf<button title="Revoke scanning access" class="px-2.5 h-9 inline-flex items-center rounded-lg text-xs font-semibold text-amber-600 border border-amber-500/30 hover:bg-amber-500/10">Revoke</button></form>
                                        @else
                                            <form method="POST" action="{{ route('users.approve', $user) }}" class="inline-flex">@csrf<button title="Approve" class="px-2.5 h-9 inline-flex items-center rounded-lg text-xs font-semibold text-emerald-600 border border-emerald-500/30 hover:bg-emerald-500/10">Approve</button></form>
                                        @endif
                                    @endcan
                                    <x-act.view :href="route('users.show', $user)" />
                                    @can('users.update')<x-act.edit :href="route('users.edit', $user)" />@endcan
                                    @can('users.delete')<x-act.delete :action="route('users.destroy', $user)" confirm="Delete this user?" />@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $users->withQueryString()->links() }}</div>
@endsection
