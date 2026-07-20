@extends('layouts.app')
@section('title', $city->exists ? 'Edit city' : 'New city')

@section('content')
    <a href="{{ route('cities.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to cities</a>

    <form method="POST" action="{{ $city->exists ? route('cities.update', $city) : route('cities.store') }}" class="mt-4 max-w-xl">
        @csrf
        @if ($city->exists) @method('PUT') @endif
        <div class="lux-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium mb-1.5">State</label>
                <select name="state_id" required class="w-full lux-field px-3.5 py-2.5">
                    <option value="">— Select a state —</option>
                    @foreach ($states as $s)
                        <option value="{{ $s->id }}" @selected(old('state_id', $city->state_id) == $s->id)>{{ $s->name }}@if ($s->country) ({{ $s->country->name }})@endif</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5">Name</label>
                <input name="name" value="{{ old('name', $city->name) }}" required class="w-full lux-field px-3.5 py-2.5">
            </div>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Sort order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $city->sort_order ?? 0) }}" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Status</label>
                    <select name="status" class="w-full lux-field px-3.5 py-2.5">
                        <option value="active" @selected(old('status', $city->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $city->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5">{{ $city->exists ? 'Update' : 'Create' }} city</button>
            <a href="{{ route('cities.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
