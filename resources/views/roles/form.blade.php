@extends('layouts.app')
@section('title', $role->exists ? 'Edit role' : 'New role')

@section('content')
    <a href="{{ route('roles.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to roles</a>

    <form method="POST" action="{{ $role->exists ? route('roles.update', $role) : route('roles.store') }}" class="mt-4 max-w-3xl" x-data>
        @csrf
        @if ($role->exists) @method('PUT') @endif
        <div class="lux-card p-6 space-y-6">
            @if (! $role->exists)
                <div class="max-w-sm">
                    <label class="block text-sm font-medium mb-1.5">Role key</label>
                    <input name="name" value="{{ old('name') }}" required placeholder="e.g. regional-manager" class="w-full lux-field px-3.5 py-2.5">
                    <p class="text-xs text-slate-400 mt-1">Lowercase letters, numbers and hyphens only.</p>
                </div>
            @else
                <div>
                    <div class="text-sm text-slate-400">Role</div>
                    <div class="text-lg font-semibold">{{ ucwords(str_replace('-', ' ', $role->name)) }}</div>
                </div>
            @endif

            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-medium">Permissions</label>
                    <label class="flex items-center gap-2 text-xs text-slate-500">
                        <input type="checkbox" @click="$root.querySelectorAll('input[name=\'permissions[]\']').forEach(c => c.checked = $event.target.checked)" class="rounded"> Select all
                    </label>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($grouped as $module => $permissions)
                        <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                            <div class="font-medium text-sm mb-2 capitalize">{{ str_replace('-', ' ', $module) }}</div>
                            <div class="space-y-1.5">
                                @foreach ($permissions as $permission)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', $assigned), true)) class="rounded text-brand-600 focus:ring-brand-500">
                                        {{ \Illuminate\Support\Str::after($permission->name, '.') }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5">{{ $role->exists ? 'Update permissions' : 'Create role' }}</button>
            <a href="{{ route('roles.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
