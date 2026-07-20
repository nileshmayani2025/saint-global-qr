@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">
            {{ $recipients->total() }} notification(s)@if ($unreadCount > 0) · <span class="text-brand-500 font-medium">{{ $unreadCount }} unread</span>@endif
        </p>
        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('my.notifications.read-all') }}">
                @csrf
                <button class="lux-ghost px-4 py-2 text-sm">Mark all as read</button>
            </form>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($recipients as $row)
            <a href="{{ route('my.notifications.read', $row) }}"
               class="lux-card block p-5 hover:border-brand-400 transition {{ $row->read_at ? 'opacity-70' : '' }}">
                <div class="flex items-start gap-3">
                    @unless ($row->read_at)
                        <span class="mt-2 w-2 h-2 rounded-full bg-brand-500 shrink-0"></span>
                    @endunless
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-semibold">{{ $row->pushNotification?->title }}</h3>
                            <span class="text-xs text-[var(--muted)] shrink-0">{{ $row->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 text-sm text-[var(--muted)] whitespace-pre-line">{{ $row->pushNotification?->body }}</p>

                        @if ($row->pushNotification?->image_path)
                            <img src="{{ asset('media/'.$row->pushNotification->image_path) }}" alt=""
                                 class="mt-3 rounded-lg max-h-48 w-full object-cover">
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="lux-card p-10 text-center">
                <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="mt-3 text-slate-400">You have no notifications yet.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $recipients->links() }}</div>
@endsection
