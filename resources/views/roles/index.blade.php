@extends('layouts.app')
@section('title', 'Roles')

@section('content')
    @if ($missingPermissions !== [])
        <div class="mb-5 lux-card p-5 border-l-4 border-l-amber-500">
            <div class="flex items-start gap-3">
                <span class="w-9 h-9 shrink-0 grid place-items-center rounded-lg bg-amber-500/10 text-amber-500">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="font-semibold">{{ count($missingPermissions) }} permission(s) are missing from the database</h3>
                    <p class="mt-1 text-sm text-[var(--muted)]">
                        These modules exist in the app but cannot be granted to anyone until their permissions are created,
                        which is why they do not appear in the list below — or in the menus.
                    </p>
                    <p class="mt-2 text-xs text-[var(--muted)] break-words">{{ implode(', ', $missingPermissions) }}</p>

                    @can('roles.create')
                        <form method="POST" action="{{ route('roles.sync-permissions') }}" class="mt-3">
                            @csrf
                            <button class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">Add missing permissions</button>
                        </form>
                        <p class="mt-2 text-xs text-[var(--muted)]">
                            Only adds — no existing role assignment is changed.
                        </p>
                    @endcan
                </div>
            </div>
        </div>
    @endif

    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $roles->total() }} role(s)</p>
        @can('roles.create')
            <a href="{{ route('roles.create') }}" class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">+ New role</a>
        @endcan
    </div>

    <div class="lux-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-transparent text-left text-slate-500 dark:text-slate-400">
                    <tr><th class="px-4 py-3 font-medium">Role</th><th class="px-4 py-3 font-medium">Permissions</th><th class="px-4 py-3 font-medium">Users</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($roles as $role)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-medium">{{ ucwords(str_replace('-', ' ', $role->name)) }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $role->permissions_count }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $role->users_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('roles.update')<x-act.edit :href="route('roles.edit', $role)" />@endcan
                                    @can('roles.delete')<x-act.delete :action="route('roles.destroy', $role)" confirm="Delete this role?" />@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No roles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $roles->withQueryString()->links() }}</div>
@endsection
