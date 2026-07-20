@extends('layouts.app')
@section('title', $notification->exists ? 'Edit notification' : 'New notification')

@section('content')
    <a href="{{ route('push-notifications.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to notifications</a>

    @php
        $filters = $notification->audience_filters ?? [];
        $selectedAudience = old('audience', $notification->audience ?? 'all');
    @endphp

    <form method="POST" enctype="multipart/form-data" class="mt-4 max-w-2xl"
          action="{{ $notification->exists ? route('push-notifications.update', $notification) : route('push-notifications.store') }}"
          x-data="{ audience: '{{ $selectedAudience }}' }">
        @csrf
        @if ($notification->exists) @method('PUT') @endif

        <div class="lux-card p-6 space-y-5">
            <h3 class="font-semibold">Message</h3>

            <div>
                <label class="block text-sm font-medium mb-1.5">Title</label>
                <input name="title" maxlength="120" required value="{{ old('title', $notification->title) }}"
                       class="w-full lux-field px-3.5 py-2.5" placeholder="New reward scheme is live">
                <p class="mt-1 text-xs text-slate-400">Keep it short — phones truncate long titles.</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1.5">Message</label>
                <textarea name="body" rows="3" maxlength="500" required class="w-full lux-field px-3.5 py-2.5"
                          placeholder="Scan any Saint Globle pack this month and earn double points.">{{ old('body', $notification->body) }}</textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Link <span class="text-slate-400">(optional)</span></label>
                    <input name="action_url" value="{{ old('action_url', $notification->action_url) }}"
                           class="w-full lux-field px-3.5 py-2.5" placeholder="/my/rewards">
                    <p class="mt-1 text-xs text-slate-400">Where tapping the notification goes. Relative or full URL.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Image <span class="text-slate-400">(optional)</span></label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-brand-500/10 file:text-brand-600">
                    @if ($notification->image_path)
                        <label class="mt-1.5 flex items-center gap-2 text-xs text-slate-500">
                            <input type="checkbox" name="remove_image" value="1" class="rounded text-rose-500 focus:ring-rose-400">
                            Remove current image
                        </label>
                    @endif
                </div>
            </div>
        </div>

        <div class="lux-card p-6 space-y-5 mt-6">
            <h3 class="font-semibold">Audience</h3>

            <div>
                <label class="block text-sm font-medium mb-1.5">Send to</label>
                <select name="audience" x-model="audience" class="w-full lux-field px-3.5 py-2.5 no-s2">
                    <option value="all">All active users</option>
                    <option value="role">Users with a specific role</option>
                    <option value="users">Specific users</option>
                    <option value="location">Users in a location</option>
                </select>
            </div>

            {{-- Role picker --}}
            <div x-show="audience === 'role'" x-cloak>
                <label class="block text-sm font-medium mb-2">Roles</label>
                <div class="grid sm:grid-cols-3 gap-2">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-2">
                            <input type="checkbox" name="roles[]" value="{{ $role }}"
                                   @checked(in_array($role, old('roles', $filters['roles'] ?? []), true))
                                   class="rounded text-brand-600 focus:ring-brand-500">
                            {{ ucwords(str_replace('-', ' ', $role)) }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Specific users --}}
            <div x-show="audience === 'users'" x-cloak>
                <label class="block text-sm font-medium mb-1.5">Users</label>
                <select name="user_ids[]" multiple size="8" class="w-full lux-field px-3.5 py-2.5">
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected(in_array($u->id, old('user_ids', $filters['user_ids'] ?? [])))>
                            {{ $u->name }} — {{ $u->phone }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-400">Search and pick one or more users.</p>
            </div>

            {{-- Location --}}
            <div x-show="audience === 'location'" x-cloak class="space-y-5">
                @include('partials.location-fields', [
                    'locationOwner' => (object) [
                        'country_id' => $filters['country_id'] ?? null,
                        'state_id' => $filters['state_id'] ?? null,
                        'city_id' => $filters['city_id'] ?? null,
                        'address' => null,
                    ],
                    'hideAddress' => true,
                ])
                <p class="text-xs text-slate-400">Leave a level blank to include everything below it — picking only a state sends to every city in that state.</p>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3">
            <button name="send" value="0" class="lux-ghost px-5 py-2.5 font-medium">
                {{ $notification->exists ? 'Save changes' : 'Save as draft' }}
            </button>
            @can('notifications.send')
                <button name="send" value="1" class="rounded-lg lux-btn text-white font-medium px-5 py-2.5"
                        onclick="return confirm('Send this notification now? It cannot be recalled.')">
                    Save &amp; send now
                </button>
            @endcan
            <a href="{{ route('push-notifications.index') }}" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
@endsection
