@extends('layouts.app')
@section('title', $state->exists ? 'Edit state' : 'New state')

@section('content')
    <a href="{{ route('states.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to states</a>

    <form method="POST" action="{{ $state->exists ? route('states.update', $state) : route('states.store') }}" class="mt-4 max-w-xl">
        @csrf
        @if ($state->exists) @method('PUT') @endif
        <div class="lux-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium mb-1.5">Country</label>
                <select name="country_id" required class="w-full lux-field px-3.5 py-2.5">
                    <option value="">— Select a country —</option>
                    @foreach ($countries as $c)
                        <option value="{{ $c->id }}" @selected(old('country_id', $state->country_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5">Name</label>
                <input name="name" value="{{ old('name', $state->name) }}" required class="w-full lux-field px-3.5 py-2.5">
            </div>
            <div class="grid grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Code</label>
                    <input name="code" maxlength="10" value="{{ old('code', $state->code) }}" placeholder="GJ" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Sort order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $state->sort_order ?? 0) }}" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Status</label>
                    <select name="status" class="w-full lux-field px-3.5 py-2.5">
                        <option value="active" @selected(old('status', $state->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $state->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5">{{ $state->exists ? 'Update' : 'Create' }} state</button>
            <a href="{{ route('states.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
