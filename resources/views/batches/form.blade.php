@extends('layouts.app')
@section('title', $batch->exists ? 'Edit batch' : 'New batch')

@section('content')
    <a href="{{ route('batches.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to batches</a>

    <form method="POST" action="{{ $batch->exists ? route('batches.update', $batch) : route('batches.store') }}" class="mt-4 max-w-xl">
        @csrf
        @if ($batch->exists) @method('PUT') @endif
        <div class="lux-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium mb-1.5">Product</label>
                <select name="product_id" required class="w-full lux-field px-3.5 py-2.5">
                    <option value="">— Select product —</option>
                    @foreach ($products as $p)<option value="{{ $p->id }}" @selected(old('product_id', $batch->product_id) == $p->id)>{{ $p->name }} ({{ $p->sku }})</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5">Batch code</label>
                <input name="code" value="{{ old('code', $batch->code) }}" required class="w-full lux-field px-3.5 py-2.5">
            </div>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Manufacture date</label>
                    <input type="date" name="manufacture_date" value="{{ old('manufacture_date', optional($batch->manufacture_date)->toDateString()) }}" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Expiry date</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date', optional($batch->expiry_date)->toDateString()) }}" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Quantity</label>
                    <input type="number" min="1" name="quantity" value="{{ old('quantity', $batch->quantity ?? 100) }}" required class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Reward points <span class="text-slate-400">(optional)</span></label>
                    <input type="number" min="0" name="reward_points" value="{{ old('reward_points', $batch->reward_points) }}" class="w-full lux-field px-3.5 py-2.5">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5">Status</label>
                <select name="status" class="w-full lux-field px-3.5 py-2.5">
                    @foreach (['draft','generating','active','closed'] as $s)<option value="{{ $s }}" @selected(old('status', $batch->status ?? 'draft') === $s)>{{ ucfirst($s) }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5">{{ $batch->exists ? 'Update' : 'Create' }} batch</button>
            <a href="{{ route('batches.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
