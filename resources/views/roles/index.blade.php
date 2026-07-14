@extends('layouts.app')
@section('title', 'Roles')

@section('content')
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
