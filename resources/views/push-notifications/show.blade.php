@extends('layouts.app')
@section('title', $notification->title)

@section('content')
    <a href="{{ route('push-notifications.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to notifications</a>

    <div class="mt-4 grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="lux-card p-6">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-lg font-bold">{{ $notification->title }}</h2>
                    <x-badge :status="$notification->status" />
                </div>
                <p class="mt-2 text-sm text-[var(--muted)] whitespace-pre-line">{{ $notification->body }}</p>

                @if ($notification->image_path)
                    <img src="{{ asset('media/'.$notification->image_path) }}" alt=""
                         class="mt-4 rounded-xl max-h-64 w-full object-cover">
                @endif

                @if ($notification->action_url)
                    <p class="mt-4 text-sm">
                        <span class="text-slate-400">Opens:</span>
                        <span class="font-medium break-all">{{ $notification->action_url }}</span>
                    </p>
                @endif
            </div>

            @if ($notification->status === 'failed' && $notification->failure_reason)
                <div class="lux-card p-6 border-l-4 border-l-rose-500">
                    <h3 class="font-semibold text-rose-500 mb-1">Delivery failed</h3>
                    <p class="text-sm text-[var(--muted)] break-words">{{ $notification->failure_reason }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="lux-card p-6">
                <h3 class="font-semibold mb-4">Delivery</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-400">Audience</dt><dd class="font-medium text-right">{{ $audienceLabel }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">Users reached</dt><dd class="font-medium">{{ $notification->recipient_count }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">Devices pushed</dt><dd class="font-medium">{{ $notification->sent_count }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">Failed devices</dt><dd class="font-medium {{ $notification->failed_count > 0 ? 'text-rose-500' : '' }}">{{ $notification->failed_count }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">Opened</dt><dd class="font-medium">{{ $readCount }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">Sent</dt><dd class="font-medium">{{ optional($notification->sent_at)->diffForHumans() ?? 'Not yet' }}</dd></div>
                </dl>
            </div>

            @can('send', $notification)
                <form method="POST" action="{{ route('push-notifications.send', $notification) }}"
                      onsubmit="return confirm('Send this notification now? It cannot be recalled.')">
                    @csrf
                    <button class="w-full rounded-lg lux-btn text-white font-medium px-5 py-2.5">
                        {{ $notification->status === 'failed' ? 'Retry send' : 'Send now' }}
                    </button>
                </form>
            @endcan
        </div>
    </div>
@endsection
