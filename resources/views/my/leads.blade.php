@extends('layouts.consumer')
@section('title', 'My Leads')

@section('content')
    <div class="max-w-xl mx-auto">
    <div class="flex items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="font-display font-bold text-xl leading-tight">My Leads</h1>
            <p class="text-sm text-[var(--muted)]">{{ $leads->total() }} added by you</p>
        </div>
        <a href="{{ route('my.leads.create') }}" class="shrink-0 rounded-lg lux-btn text-white text-sm font-medium px-4 py-2.5">+ Add</a>
    </div>

    <div class="space-y-3">
        @forelse ($leads as $lead)
            <div class="lux-card p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="font-semibold truncate">{{ $lead->name }}</h2>
                        <a href="tel:{{ $lead->phone }}" class="text-sm text-brand-500">{{ $lead->phone }}</a>
                        @php $place = collect([$lead->city?->name, $lead->state?->name])->filter()->implode(', '); @endphp
                        @if ($place !== '')
                            <p class="mt-0.5 text-xs text-[var(--muted)]">{{ $place }}</p>
                        @endif
                    </div>
                    <x-badge :status="$lead->status" />
                </div>

                @if ($lead->remark)
                    <p class="mt-2 text-sm text-[var(--muted)]">{{ $lead->remark }}</p>
                @endif

                <div class="mt-3 flex items-center gap-2">
                    <a href="tel:{{ $lead->phone }}" class="flex-1 text-center rounded-lg lux-ghost py-2 text-sm font-medium">Call</a>
                    <a href="https://wa.me/91{{ $lead->phone }}" target="_blank" rel="noopener"
                       class="flex-1 text-center rounded-lg bg-[#25D366] text-white py-2 text-sm font-medium">WhatsApp</a>
                </div>

                <p class="mt-2 text-[11px] text-[var(--muted)]">{{ $lead->created_at?->diffForHumans() }}</p>
            </div>
        @empty
            <div class="lux-card p-10 text-center">
                <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 8a3 3 0 11-6 0 3 3 0 016 0zm-3 5a6 6 0 00-6 6h12a6 6 0 00-6-6z"/>
                </svg>
                <p class="mt-3 text-slate-400">No leads yet.</p>
                <a href="{{ route('my.leads.create') }}" class="mt-4 inline-block rounded-lg lux-btn text-white text-sm font-medium px-5 py-2.5">Add your first lead</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $leads->links() }}</div>
    </div>
@endsection
