@extends('layouts.app')
@section('title', $userModel->exists ? 'Edit user' : 'New user')

@section('content')
    <a href="{{ route('users.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to users</a>

    <form method="POST" action="{{ $userModel->exists ? route('users.update', $userModel) : route('users.store') }}" class="mt-4 max-w-2xl">
        @csrf
        @if ($userModel->exists) @method('PUT') @endif
        <div class="lux-card p-6 space-y-5">
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Name</label>
                    <input name="name" value="{{ old('name', $userModel->name) }}" required class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Mobile number</label>
                    <input name="phone" type="tel" inputmode="numeric" maxlength="10" required
                           value="{{ old('phone', $userModel->phone) }}" class="w-full lux-field px-3.5 py-2.5">
                    <p class="mt-1 text-xs text-slate-400">Used to sign in — 10 digits, no +91.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Email <span class="text-slate-400">(optional)</span></label>
                    <input type="email" name="email" value="{{ old('email', $userModel->email) }}" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Status</label>
                    <select name="status" class="w-full lux-field px-3.5 py-2.5">
                        @foreach (['active','inactive','suspended'] as $s)<option value="{{ $s }}" @selected(old('status', $userModel->status ?? 'active') === $s)>{{ ucfirst($s) }}</option>@endforeach
                    </select>
                </div>
                @if ($companies->isNotEmpty())
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium mb-1.5">Company</label>
                        <select name="company_id" class="w-full lux-field px-3.5 py-2.5">
                            <option value="">— None —</option>
                            @foreach ($companies as $c)<option value="{{ $c->id }}" @selected(old('company_id', $userModel->company_id) == $c->id)>{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div class="lux-divider"></div>
            <div class="space-y-5">
                <p class="text-sm font-semibold">Location</p>
                @include('partials.location-fields', ['locationOwner' => $userModel])
            </div>
            <div class="lux-divider"></div>

            <div>
                <label class="block text-sm font-medium mb-2">Roles</label>
                <div class="grid sm:grid-cols-3 gap-2">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-2">
                            <input type="checkbox" name="roles[]" value="{{ $role }}" @checked(in_array($role, old('roles', $assignedRoles), true)) class="rounded text-brand-600 focus:ring-brand-500">
                            {{ ucwords(str_replace('-', ' ', $role)) }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5">{{ $userModel->exists ? 'Update' : 'Create' }} user</button>
            <a href="{{ route('users.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
