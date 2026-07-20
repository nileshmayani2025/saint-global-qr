@extends('layouts.app')
@section('title', $lead->name)

@section('content')
    <a href="{{ route('leads.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to leads</a>

    <div class="mt-4 grid lg:grid-cols-3 gap-6">
        <div class="lux-card p-6 text-center h-fit">
            <div class="w-20 h-20 mx-auto rounded-full grid place-items-center text-2xl font-bold text-white"
                 style="background:linear-gradient(135deg,#2ca0d4,#1b5a7c)">{{ strtoupper(substr($lead->name, 0, 1)) }}</div>
            <h2 class="mt-4 text-lg font-bold">{{ $lead->name }}</h2>
            <div class="mt-2"><x-badge :status="$lead->status" /></div>

            <div class="mt-5 flex flex-col gap-2">
                <a href="tel:{{ $lead->phone }}" class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2.5">
                    Call {{ $lead->phone }}
                </a>
                <a href="https://wa.me/91{{ $lead->phone }}" target="_blank" rel="noopener"
                   class="rounded-lg bg-[#25D366] text-white text-sm font-medium px-4 py-2.5 hover:opacity-90 transition">
                    WhatsApp
                </a>
            </div>

            @can('update', $lead)
                <a href="{{ route('leads.edit', $lead) }}" class="mt-4 inline-block text-sm text-brand-600 hover:underline">Edit lead</a>
            @endcan
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="lux-card p-6">
                <h3 class="font-semibold mb-4">Location</h3>
                <dl class="grid sm:grid-cols-3 gap-4 text-sm">
                    <div><dt class="text-slate-400">Country</dt><dd class="font-medium">{{ $lead->country?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">State</dt><dd class="font-medium">{{ $lead->state?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">City</dt><dd class="font-medium">{{ $lead->city?->name ?? '—' }}</dd></div>
                    <div class="sm:col-span-3"><dt class="text-slate-400">Address</dt><dd class="font-medium whitespace-pre-line">{{ $lead->address ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="lux-card p-6">
                <h3 class="font-semibold mb-3">Remark</h3>
                <p class="text-sm text-[var(--muted)] whitespace-pre-line">{{ $lead->remark ?: 'No remark recorded.' }}</p>
            </div>

            <div class="lux-card p-6">
                <h3 class="font-semibold mb-4">Record</h3>
                <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-slate-400">Added by</dt><dd class="font-medium">{{ $lead->createdBy?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">Added on</dt><dd class="font-medium">{{ $lead->created_at?->format('d M Y, g:i a') }}</dd></div>
                    @if ($lead->company)
                        <div><dt class="text-slate-400">Company</dt><dd class="font-medium">{{ $lead->company->name }}</dd></div>
                    @endif
                    <div><dt class="text-slate-400">Last updated</dt><dd class="font-medium">{{ $lead->updated_at?->diffForHumans() }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
@endsection
