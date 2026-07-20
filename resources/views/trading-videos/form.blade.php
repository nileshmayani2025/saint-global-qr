@extends('layouts.app')
@section('title', $video->exists ? 'Edit trading video' : 'New trading video')

@section('content')
    <a href="{{ route('trading-videos.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to trading videos</a>

    <form method="POST" class="mt-4 max-w-xl"
          action="{{ $video->exists ? route('trading-videos.update', $video) : route('trading-videos.store') }}">
        @csrf
        @if ($video->exists) @method('PUT') @endif

        <div class="lux-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium mb-1.5">Product</label>
                <select name="product_id" required class="w-full lux-field px-3.5 py-2.5">
                    <option value="">— Select a product —</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" @selected(old('product_id', $video->product_id) == $p->id)>
                            {{ $p->name }}@if ($p->sku) ({{ $p->sku }})@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1.5">Video URL</label>
                <input name="url" type="url" required value="{{ old('url', $video->url) }}"
                       placeholder="https://www.youtube.com/watch?v=..." class="w-full lux-field px-3.5 py-2.5">
                <p class="mt-1 text-xs text-slate-400">YouTube and Vimeo links play inside the app. Any other link opens in the browser.</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1.5">Title <span class="text-slate-400">(optional)</span></label>
                <input name="title" value="{{ old('title', $video->title) }}" class="w-full lux-field px-3.5 py-2.5"
                       placeholder="Leave blank to use the product name">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1.5">Description <span class="text-slate-400">(optional)</span></label>
                <textarea name="description" rows="3" class="w-full lux-field px-3.5 py-2.5">{{ old('description', $video->description) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Sort order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $video->sort_order ?? 0) }}" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Status</label>
                    <select name="status" class="w-full lux-field px-3.5 py-2.5">
                        <option value="active" @selected(old('status', $video->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $video->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>

            <p class="text-xs text-slate-400">
                The newest active video is shown to every app user as a welcome popup. Publishing a new one makes it pop up again.
            </p>
        </div>

        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5">{{ $video->exists ? 'Update' : 'Create' }} video</button>
            <a href="{{ route('trading-videos.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
