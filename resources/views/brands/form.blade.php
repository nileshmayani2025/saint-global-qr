@extends('layouts.app')
@section('title', $brand->exists ? 'Edit brand' : 'New brand')

@section('content')
    <a href="{{ route('brands.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to brands</a>

    <form method="POST" action="{{ $brand->exists ? route('brands.update', $brand) : route('brands.store') }}" class="mt-4 max-w-xl">
        @csrf
        @if ($brand->exists) @method('PUT') @endif
        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium mb-1.5">Name</label>
                <input name="name" value="{{ old('name', $brand->name) }}" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">{{ old('description', $brand->description) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Sort order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $brand->sort_order ?? 0) }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Status</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                        <option value="active" @selected(old('status', $brand->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $brand->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium px-5 py-2.5">{{ $brand->exists ? 'Update' : 'Create' }} brand</button>
            <a href="{{ route('brands.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
