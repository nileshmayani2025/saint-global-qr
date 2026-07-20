@extends('layouts.app')
@section('title', $lead->exists ? 'Edit lead' : 'New lead')

@section('content')
    <a href="{{ route('leads.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to leads</a>

    <form method="POST" class="mt-4 max-w-2xl"
          action="{{ $lead->exists ? route('leads.update', $lead) : route('leads.store') }}">
        @csrf
        @if ($lead->exists) @method('PUT') @endif

        <div class="lux-card p-6 space-y-5">
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Name</label>
                    <input name="name" required value="{{ old('name', $lead->name) }}" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Mobile number</label>
                    <input name="phone" type="tel" inputmode="numeric" maxlength="10" required
                           value="{{ old('phone', $lead->phone) }}" class="w-full lux-field px-3.5 py-2.5">
                    <p class="mt-1 text-xs text-slate-400">10 digits, no +91.</p>
                </div>
            </div>

            <div class="lux-divider"></div>

            <div class="space-y-5">
                <p class="text-sm font-semibold">Location</p>
                @include('partials.location-fields', ['locationOwner' => $lead])
            </div>

            <div class="lux-divider"></div>

            <div>
                <label class="block text-sm font-medium mb-1.5">Remark</label>
                <textarea name="remark" rows="4" class="w-full lux-field px-3.5 py-2.5"
                          placeholder="What did they ask about? Any follow-up planned?">{{ old('remark', $lead->remark) }}</textarea>
            </div>

            <div class="sm:max-w-xs">
                <label class="block text-sm font-medium mb-1.5">Status</label>
                <select name="status" class="w-full lux-field px-3.5 py-2.5">
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected(old('status', $lead->status ?? 'new') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5">{{ $lead->exists ? 'Update' : 'Create' }} lead</button>
            <a href="{{ route('leads.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
