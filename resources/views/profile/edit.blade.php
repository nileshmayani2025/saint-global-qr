@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
    <div class="max-w-5xl space-y-6">

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid lg:grid-cols-3 gap-6 items-start">

                {{-- Identity --}}
                <div class="lux-card p-6">
                    <div class="text-center">
                        @if ($userModel->avatar_path)
                            <img src="{{ asset('media/'.$userModel->avatar_path) }}" alt="{{ $userModel->name }}"
                                 class="w-24 h-24 mx-auto rounded-full object-cover ring-2 ring-brand-500/30">
                        @else
                            <div class="w-24 h-24 mx-auto rounded-full grid place-items-center text-3xl font-bold text-white"
                                 style="background:linear-gradient(135deg,#2ca0d4,#1b5a7c)">{{ strtoupper(substr($userModel->name, 0, 1)) }}</div>
                        @endif

                        <h2 class="mt-4 text-lg font-bold leading-tight">{{ $userModel->name }}</h2>
                        <p class="mt-1 text-sm text-[var(--muted)]">{{ $userModel->getRoleNames()->implode(', ') ?: 'No role' }}</p>
                        @if ($userModel->company)
                            <p class="mt-0.5 text-xs text-[var(--muted)]">{{ $userModel->company->name }}</p>
                        @endif
                    </div>

                    <div class="lux-divider my-5"></div>

                    <label class="block text-sm font-medium mb-2">Profile photo</label>
                    <input type="file" name="avatar" accept="image/*"
                           class="block w-full text-xs text-[var(--muted)] file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-brand-500/10 file:text-brand-600 hover:file:bg-brand-500/20 file:cursor-pointer">
                    <p class="mt-2 text-xs text-[var(--muted)]">JPG, PNG or WebP · max 2 MB</p>
                    @if ($userModel->avatar_path)
                        <label class="mt-3 flex items-center gap-2 text-xs text-[var(--muted)]">
                            <input type="checkbox" name="remove_avatar" value="1" class="rounded text-rose-500 focus:ring-rose-400">
                            Remove current photo
                        </label>
                    @endif
                </div>

                {{-- Editable details --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="lux-card p-6">
                        <h3 class="font-semibold">Account details</h3>
                        <p class="mt-1 text-sm text-[var(--muted)]">How you sign in and how we reach you.</p>

                        <div class="mt-5 grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1.5">Name</label>
                                <input name="name" value="{{ old('name', $userModel->name) }}" required
                                       class="w-full lux-field px-3.5 py-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1.5">Mobile number</label>
                                <input name="phone" type="tel" inputmode="numeric" maxlength="10" required
                                       value="{{ old('phone', $userModel->phone) }}" class="w-full lux-field px-3.5 py-2.5">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium mb-1.5">
                                    Email <span class="font-normal text-[var(--muted)]">(optional)</span>
                                </label>
                                <input type="email" name="email" value="{{ old('email', $userModel->email) }}"
                                       class="w-full lux-field px-3.5 py-2.5">
                            </div>
                        </div>

                        {{-- Kept out of the grid so it can never knock the two
                             columns out of alignment when it wraps. --}}
                        <p class="mt-3 text-xs text-[var(--muted)]">
                            Your mobile number is your sign-in ID — 10 digits, without +91.
                        </p>
                    </div>

                    <div class="lux-card p-6">
                        <h3 class="font-semibold">Location</h3>
                        <p class="mt-1 text-sm text-[var(--muted)]">Used on your business card and for regional updates.</p>

                        <div class="mt-5 space-y-4">
                            @include('partials.location-fields', ['locationOwner' => $userModel])
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button class="rounded-lg lux-btn text-white font-medium px-6 py-2.5">Save changes</button>
                <a href="{{ route('dashboard') }}" class="text-sm text-[var(--muted)] hover:text-[var(--text)] transition">Cancel</a>
            </div>
        </form>

        {{-- Linked features --}}
        <div class="grid md:grid-cols-2 gap-6">
            <div class="lux-card p-6 flex flex-col">
                <h3 class="font-semibold">Digital business card</h3>
                <p class="mt-2 text-sm text-[var(--muted)] flex-1">
                    A shareable card with your name, mobile, WhatsApp and address — plus a QR code
                    people can scan to open it.
                </p>
                <a href="{{ route('my.business-card.edit') }}"
                   class="mt-4 self-start rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">Manage my card</a>
            </div>

            <div class="lux-card p-6 flex flex-col">
                <h3 class="font-semibold">Notifications</h3>
                @if ($pushEnabled)
                    <p class="mt-2 text-sm text-[var(--muted)] flex-1">
                        Push notifications are on for {{ $pushDeviceCount }} device(s).
                        Turn them off from your browser's site settings.
                    </p>
                    <span class="mt-4 self-start inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-medium px-3 py-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Enabled
                    </span>
                @else
                    <p class="mt-2 text-sm text-[var(--muted)] flex-1">
                        Get notified about new reward schemes and updates, even when the app is closed.
                    </p>
                    <button type="button" onclick="enablePushNotifications(this)"
                            class="mt-4 self-start rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">
                        Enable notifications
                    </button>
                @endif
            </div>
        </div>

        {{-- Site-wide support numbers — admins only --}}
        @if ($canManageSettings)
            <form method="POST" action="{{ route('profile.support-contacts') }}">
                @csrf
                @method('PUT')

                <div class="lux-card p-6">
                    <div class="flex items-start gap-3">
                        <span class="w-9 h-9 shrink-0 grid place-items-center rounded-lg bg-amber-500/10 text-amber-500">
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728m-12.728 0a9 9 0 010-12.728m9.9 9.9a5 5 0 010-7.072m-7.072 0a5 5 0 010 7.072M13 12a1 1 0 11-2 0 1 1 0 012 0z"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="font-semibold">Support contact numbers</h3>
                            <p class="mt-1 text-sm text-[var(--muted)]">
                                Shown as the floating call and WhatsApp buttons at the bottom of every page,
                                in both the panel and the app. Leave a field blank to hide that button.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1.5">Helpline number</label>
                            <input name="helpline" value="{{ old('helpline', $supportHelpline) }}"
                                   placeholder="+91 98765 43210" class="w-full lux-field px-3.5 py-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1.5">WhatsApp number</label>
                            <input name="whatsapp" value="{{ old('whatsapp', $supportWhatsapp) }}"
                                   placeholder="+91 98765 43210" class="w-full lux-field px-3.5 py-2.5">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium mb-1.5">
                                WhatsApp opening message <span class="font-normal text-[var(--muted)]">(optional)</span>
                            </label>
                            <input name="whatsapp_message" value="{{ old('whatsapp_message', $supportMessage) }}"
                                   placeholder="Hello, I need help with Saint Globle products."
                                   class="w-full lux-field px-3.5 py-2.5">
                            <p class="mt-2 text-xs text-[var(--muted)]">Pre-filled in the chat when someone taps the WhatsApp button.</p>
                        </div>
                    </div>

                    <button class="mt-5 rounded-lg lux-btn text-white font-medium px-6 py-2.5">Save support numbers</button>
                </div>
            </form>
        @endif
    </div>
@endsection
