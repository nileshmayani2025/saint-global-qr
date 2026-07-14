@extends('layouts.app')
@section('title', 'QR Codes')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $qrCodes->total() }} QR code(s)</p>
    </div>

    <form method="GET" class="mb-4 grid sm:grid-cols-3 gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search code…" class="lux-field px-3 py-2 text-sm">
        <select name="status" class="lux-field px-3 py-2 text-sm">
            <option value="">Any status</option>
            @foreach (['generated','printed','active','verified','blocked'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? null) === $s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
        <button class="lux-field px-3 py-2 text-sm font-medium">Filter</button>
    </form>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse ($qrCodes as $qr)
            <div class="lux-card p-4">
                <a href="{{ route('qr-codes.show', $qr) }}" class="block">
                    @if ($qr->image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($qr->image_path) }}" class="w-full aspect-square object-contain rounded-lg bg-white p-2 border border-slate-100 dark:border-slate-800">
                    @else
                        <div class="w-full aspect-square rounded-lg bg-slate-100 dark:bg-slate-800 grid place-items-center text-slate-400 text-xs">No image</div>
                    @endif
                </a>
                <div class="mt-3">
                    <div class="font-mono text-xs truncate" title="{{ $qr->code }}">{{ $qr->code }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ $qr->product?->name }} · #{{ $qr->serial }}</div>
                    <div class="mt-2 flex items-center justify-between">
                        <x-badge :status="$qr->status" />
                        <span class="text-xs text-slate-400">{{ $qr->scan_count }} scans</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full lux-card py-10 text-center text-slate-400">No QR codes found.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $qrCodes->withQueryString()->links() }}</div>
@endsection
