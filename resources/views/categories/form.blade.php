@extends('layouts.app')
@section('title', $category->exists ? 'Edit category' : 'New category')

@section('content')
    <a href="{{ route('categories.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to categories</a>

    <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="mt-4 max-w-xl">
        @csrf
        @if ($category->exists) @method('PUT') @endif
        <div class="lux-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium mb-1.5">Name</label>
                <input name="name" value="{{ old('name', $category->name) }}" required class="w-full lux-field px-3.5 py-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5">Parent category</label>
                <select name="parent_id" class="w-full lux-field px-3.5 py-2.5">
                    <option value="">— None (top level) —</option>
                    @foreach ($parents as $p)<option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id) == $p->id)>{{ $p->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5">Description</label>
                <textarea name="description" rows="3" class="w-full lux-field px-3.5 py-2.5">{{ old('description', $category->description) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Sort order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Status</label>
                    <select name="status" class="w-full lux-field px-3.5 py-2.5">
                        <option value="active" @selected(old('status', $category->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $category->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5">{{ $category->exists ? 'Update' : 'Create' }} category</button>
            <a href="{{ route('categories.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
