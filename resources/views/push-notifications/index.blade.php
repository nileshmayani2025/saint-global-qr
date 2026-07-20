@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
    @unless ($firebaseReady)
        <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-900/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
            <p class="font-semibold mb-0.5">Firebase is not configured yet.</p>
            <p>Notifications will still be saved and appear in each user's in-app inbox, but no push will be delivered to devices. Set <code>FIREBASE_PROJECT_ID</code> and the service-account file in <code>.env</code> — see <code>DEPLOY.md</code>.</p>
        </div>
    @endunless

    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $notifications->total() }} notification(s)</p>
        @can('notifications.create')
            <a href="{{ route('push-notifications.create') }}" class="rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">+ New notification</a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search title or message…" class="lux-field px-3 py-2 text-sm flex-1 max-w-xs">
        <select name="status" class="lux-field px-3 py-2 text-sm max-w-[10rem]">
            <option value="">All statuses</option>
            @foreach (['draft', 'queued', 'sending', 'sent', 'failed'] as $s)
                <option value="{{ $s }}" @selected(($filters['status'] ?? null) === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="lux-ghost px-4 py-2 text-sm">Filter</button>
    </form>

    <div class="lux-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-transparent text-left text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Audience</th>
                        <th class="px-4 py-3 font-medium">Delivered</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Sent</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($notifications as $n)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $n->title }}</div>
                                <div class="text-xs text-slate-400 line-clamp-1">{{ $n->body }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $audienceResolver->describe($n) }}</td>
                            <td class="px-4 py-3 text-slate-500">
                                @if ($n->isSent())
                                    {{ $n->recipient_count }} user(s) · {{ $n->sent_count }} device(s)
                                    @if ($n->failed_count > 0)
                                        <span class="text-rose-500">· {{ $n->failed_count }} failed</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3"><x-badge :status="$n->status" /></td>
                            <td class="px-4 py-3 text-slate-500">{{ optional($n->sent_at)->diffForHumans() ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <x-act.view :href="route('push-notifications.show', $n)" />
                                    @can('update', $n)<x-act.edit :href="route('push-notifications.edit', $n)" />@endcan
                                    @can('notifications.delete')<x-act.delete :action="route('push-notifications.destroy', $n)" confirm="Delete this notification?" />@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No notifications yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $notifications->withQueryString()->links() }}</div>
@endsection
