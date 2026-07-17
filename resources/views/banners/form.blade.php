@extends('layouts.app')
@section('title', $banner->exists ? 'Edit banner' : 'New banner')

@section('content')
    <a href="{{ route('banners.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to banners</a>

    <form method="POST" action="{{ $banner->exists ? route('banners.update', $banner) : route('banners.store') }}"
          enctype="multipart/form-data" class="mt-4 max-w-2xl">
        @csrf
        @if ($banner->exists) @method('PUT') @endif

        <div class="lux-card p-6 space-y-5">
            <div class="grid sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1.5">Title</label>
                    <input name="title" value="{{ old('title', $banner->title) }}" required
                           placeholder="Win Exciting Gifts on Scanning Products"
                           class="w-full lux-field px-3.5 py-2.5">
                    @error('title')<p class="mt-1.5 text-sm text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1.5">Subtitle <span class="text-slate-400">(optional)</span></label>
                    <input name="subtitle" value="{{ old('subtitle', $banner->subtitle) }}"
                           placeholder="From Saint Globle"
                           class="w-full lux-field px-3.5 py-2.5">
                    @error('subtitle')<p class="mt-1.5 text-sm text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Button label <span class="text-slate-400">(optional)</span></label>
                    <input name="button_label" value="{{ old('button_label', $banner->button_label) }}"
                           placeholder="Avail Now" class="w-full lux-field px-3.5 py-2.5">
                    @error('button_label')<p class="mt-1.5 text-sm text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Button link <span class="text-slate-400">(optional)</span></label>
                    <input name="link_url" value="{{ old('link_url', $banner->link_url) }}"
                           placeholder="/scan" class="w-full lux-field px-3.5 py-2.5">
                    <p class="mt-1 text-xs text-slate-400">App page like <code>/scan</code>, or a full https:// link.</p>
                    @error('link_url')<p class="mt-1.5 text-sm text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Sort order</label>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $banner->sort_order ?? 0) }}"
                           class="w-full lux-field px-3.5 py-2.5">
                    <p class="mt-1 text-xs text-slate-400">Lower number shows first.</p>
                    @error('sort_order')<p class="mt-1.5 text-sm text-rose-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Status</label>
                    <select name="status" class="w-full lux-field px-3.5 py-2.5">
                        <option value="active" @selected(old('status', $banner->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $banner->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1.5">Image <span class="text-slate-400">(optional)</span></label>
                    @if ($banner->image_path)
                        <img src="{{ asset('media/'.$banner->image_path) }}" alt="" class="w-full max-w-sm aspect-[16/7] object-cover rounded-xl mb-2 border border-slate-200 dark:border-slate-700">
                        <label class="flex items-center gap-2 text-sm mb-2"><input type="checkbox" name="remove_image" value="1" class="rounded"> Remove current image</label>
                    @endif
                    <input type="file" name="image" accept="image/*" class="w-full text-sm">
                    <p class="mt-1 text-xs text-slate-400">Wide image works best (about 16:7). Max 2 MB. Without an image the banner shows on a brand-coloured background.</p>
                    @error('image')<p class="mt-1.5 text-sm text-rose-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5">{{ $banner->exists ? 'Update' : 'Create' }} banner</button>
            <a href="{{ route('banners.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
