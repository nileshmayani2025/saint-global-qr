@extends('layouts.app')
@section('title', $country->exists ? 'Edit country' : 'New country')

@section('content')
    <a href="{{ route('countries.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to countries</a>

    <form method="POST" action="{{ $country->exists ? route('countries.update', $country) : route('countries.store') }}" class="mt-4 max-w-xl">
        @csrf
        @if ($country->exists) @method('PUT') @endif
        <div class="lux-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium mb-1.5">Name</label>
                <input name="name" value="{{ old('name', $country->name) }}" required class="w-full lux-field px-3.5 py-2.5">
            </div>
            <div class="grid grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">ISO2</label>
                    <input name="iso2" maxlength="2" value="{{ old('iso2', $country->iso2) }}" placeholder="IN" class="w-full lux-field px-3.5 py-2.5 uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">ISO3</label>
                    <input name="iso3" maxlength="3" value="{{ old('iso3', $country->iso3) }}" placeholder="IND" class="w-full lux-field px-3.5 py-2.5 uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Phone code</label>
                    <input name="phone_code" maxlength="8" value="{{ old('phone_code', $country->phone_code) }}" placeholder="+91" class="w-full lux-field px-3.5 py-2.5">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Sort order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $country->sort_order ?? 0) }}" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Status</label>
                    <select name="status" class="w-full lux-field px-3.5 py-2.5">
                        <option value="active" @selected(old('status', $country->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $country->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5">{{ $country->exists ? 'Update' : 'Create' }} country</button>
            <a href="{{ route('countries.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
