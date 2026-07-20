@extends('layouts.consumer')
@section('title', 'Add Lead')

@section('content')
    <div class="max-w-xl mx-auto">
    <a href="{{ route('my.leads.index') }}" class="text-sm text-[var(--muted)]">&larr; Back</a>
    <h1 class="mt-3 font-display font-bold text-xl">Add a Lead</h1>
    <p class="text-sm text-[var(--muted)] mb-5">Met someone interested in our products? Note them here.</p>

    <form method="POST" action="{{ route('my.leads.store') }}">
        @csrf

        <div class="lux-card p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1.5">Name</label>
                <input name="name" required value="{{ old('name') }}" class="w-full lux-field px-3.5 py-2.5">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1.5">Mobile number</label>
                <input name="phone" type="tel" inputmode="numeric" maxlength="10" required
                       value="{{ old('phone') }}" placeholder="10 digits" class="w-full lux-field px-3.5 py-2.5">
            </div>

            @include('partials.location-fields', ['locationOwner' => $lead])

            <div>
                <label class="block text-sm font-medium mb-1.5">Remark <span class="text-[var(--muted)]">(optional)</span></label>
                <textarea name="remark" rows="3" placeholder="What are they interested in?"
                          class="w-full lux-field px-3.5 py-2.5">{{ old('remark') }}</textarea>
            </div>
        </div>

        <button class="mt-5 w-full rounded-lg lux-btn text-white font-medium py-3">Save lead</button>
    </form>
    </div>
@endsection
