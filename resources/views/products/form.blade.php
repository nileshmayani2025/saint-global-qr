@extends('layouts.app')
@section('title', $product->exists ? 'Edit product' : 'New product')

@section('content')
    <a href="{{ route('products.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to products</a>

    <form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}" enctype="multipart/form-data" class="mt-4 max-w-3xl">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div class="lux-card p-6 space-y-5">
            <div class="grid sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1.5">Name</label>
                    <input name="name" value="{{ old('name', $product->name) }}" required class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">SKU</label>
                    <input name="sku" value="{{ old('sku', $product->sku) }}" required class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">HSN code</label>
                    <input name="hsn_code" value="{{ old('hsn_code', $product->hsn_code) }}" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Brand</label>
                    <select name="brand_id" class="w-full lux-field px-3.5 py-2.5">
                        <option value="">— None —</option>
                        @foreach ($brands as $b)<option value="{{ $b->id }}" @selected(old('brand_id', $product->brand_id) == $b->id)>{{ $b->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Category</label>
                    <select name="category_id" class="w-full lux-field px-3.5 py-2.5">
                        <option value="">— None —</option>
                        @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Unit</label>
                    <input name="unit" value="{{ old('unit', $product->unit ?? 'piece') }}" required class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">MRP (₹)</label>
                    <input type="number" step="0.01" min="0" name="mrp" value="{{ old('mrp', $product->mrp ?? 0) }}" required class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Reward points</label>
                    <input type="number" min="0" name="reward_points" value="{{ old('reward_points', $product->reward_points ?? 0) }}" required class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Status</label>
                    <select name="status" class="w-full lux-field px-3.5 py-2.5">
                        <option value="active" @selected(old('status', $product->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1.5">Description</label>
                    <textarea name="description" rows="3" class="w-full lux-field px-3.5 py-2.5">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1.5">Image</label>
                    @if ($product->image_path)
                        <img src="{{ asset('media/'.$product->image_path) }}" class="w-24 h-24 object-cover rounded-lg mb-2 border border-slate-200 dark:border-slate-700">
                        <label class="flex items-center gap-2 text-sm mb-2"><input type="checkbox" name="remove_image" value="1" class="rounded"> Remove current image</label>
                    @endif
                    <input type="file" name="image" accept="image/*" class="w-full text-sm">
                </div>
            </div>
        </div>

        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5">{{ $product->exists ? 'Update' : 'Create' }} product</button>
            <button name="after_save" value="continue" class="lux-ghost px-5 py-2.5">Save &amp; continue</button>
            <a href="{{ route('products.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
