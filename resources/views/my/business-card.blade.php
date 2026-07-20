@extends('layouts.app')
@section('title', 'My Business Card')

@section('content')
    @php $url = $card->publicUrl(); @endphp

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Share panel --}}
        <div class="space-y-6">
            <div class="lux-card p-6 text-center">
                <p class="text-xs uppercase tracking-wider text-[var(--muted)]">Your card link</p>

                @if ($card->isActive())
                    <img src="{{ route('card.qr', $card->slug) }}" alt="Card QR"
                         class="w-40 h-40 mx-auto mt-4 rounded-xl bg-white p-2">
                @else
                    <div class="w-40 h-40 mx-auto mt-4 rounded-xl grid place-items-center bg-slate-100 dark:bg-slate-800 text-center px-4">
                        <span class="text-xs text-[var(--muted)]">Card is switched off — turn it on to share.</span>
                    </div>
                @endif

                <div x-data="{ copied: false }" class="mt-4">
                    <input readonly value="{{ $url }}" onclick="this.select()"
                           class="w-full lux-field px-3 py-2 text-xs text-center">
                    <button type="button" class="mt-2 w-full rounded-lg lux-btn text-white text-sm font-medium py-2.5"
                            @click="navigator.clipboard.writeText('{{ $url }}').then(() => { copied = true; window.toast('Link copied.', 'success'); setTimeout(() => copied = false, 2000) })">
                        <span x-show="!copied">Copy link</span>
                        <span x-show="copied" x-cloak>Copied!</span>
                    </button>
                </div>

                <div class="mt-2 grid grid-cols-2 gap-2">
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="lux-ghost rounded-lg py-2 text-sm font-medium">Preview</a>
                    <a href="https://wa.me/?text={{ rawurlencode($userModel->name.' — '.$url) }}" target="_blank" rel="noopener"
                       class="rounded-lg bg-[#25D366] text-white py-2 text-sm font-medium">Share</a>
                </div>
            </div>

            <form method="POST" action="{{ route('my.business-card.regenerate') }}"
                  onsubmit="return confirm('Generate a new link? Anyone you already shared the old link with will no longer be able to open your card.')">
                @csrf
                <button class="w-full lux-ghost rounded-lg py-2.5 text-sm font-medium">Generate a new link</button>
                <p class="mt-1.5 text-xs text-[var(--muted)] text-center">Use this if your card has been shared somewhere you did not intend.</p>
            </form>
        </div>

        {{-- Editor --}}
        <form method="POST" action="{{ route('my.business-card.update') }}" enctype="multipart/form-data" class="lg:col-span-2">
            @csrf
            @method('PUT')

            <div class="lux-card p-6 space-y-5">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="font-semibold">Card details</h3>
                    <label class="flex items-center gap-2 text-sm shrink-0">
                        {{-- An unchecked box posts nothing, so this carries the
                             "inactive" value that the request requires. --}}
                        <input type="hidden" name="status" value="inactive">
                        <input type="checkbox" name="status" value="active" @checked(old('status', $card->status) === 'active')
                               class="rounded text-brand-600 focus:ring-brand-500">
                        Card is live
                    </label>
                </div>

                <p class="text-sm text-[var(--muted)]">
                    Your name, mobile number and address come from
                    <a href="{{ route('profile.edit') }}" class="text-brand-500 hover:underline">your profile</a>,
                    so the card always stays up to date.
                </p>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium mb-1.5">Business / firm name</label>
                        <input name="business_name" value="{{ old('business_name', $card->business_name) }}"
                               placeholder="e.g. Patel Waterproofing Works" class="w-full lux-field px-3.5 py-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5">WhatsApp number <span class="text-slate-400">(optional)</span></label>
                        <input name="whatsapp" type="tel" inputmode="numeric" maxlength="10"
                               value="{{ old('whatsapp', $card->whatsapp) }}" class="w-full lux-field px-3.5 py-2.5">
                        <p class="mt-1 text-xs text-slate-400">Leave blank to use {{ \App\Support\Phone::format($userModel->phone) }}.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1.5">Short introduction <span class="text-slate-400">(optional)</span></label>
                    <input name="tagline" maxlength="255" value="{{ old('tagline', $card->tagline) }}"
                           placeholder="e.g. 15 years of waterproofing &amp; tiling across Gujarat" class="w-full lux-field px-3.5 py-2.5">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1.5">Email on card <span class="text-slate-400">(optional)</span></label>
                    <input name="email" type="email" value="{{ old('email', $card->email) }}"
                           placeholder="{{ $userModel->email ?: 'you@example.com' }}" class="w-full lux-field px-3.5 py-2.5">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1.5">Card photo <span class="text-slate-400">(optional)</span></label>
                    <div class="flex items-center gap-4">
                        @if ($card->photoUrl())
                            <img src="{{ $card->photoUrl() }}" alt="" class="w-14 h-14 rounded-full object-cover ring-1 ring-[var(--border)]">
                        @endif
                        <div class="flex-1">
                            <input type="file" name="photo" accept="image/*"
                                   class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-brand-500/10 file:text-brand-600">
                            <p class="mt-1 text-xs text-slate-400">JPG, PNG or WebP up to 2 MB. Falls back to your profile photo.</p>
                            @if ($card->photo_path)
                                <label class="mt-1.5 flex items-center gap-2 text-xs text-slate-500">
                                    <input type="checkbox" name="remove_photo" value="1" class="rounded text-rose-500 focus:ring-rose-400">
                                    Remove card photo
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex items-center gap-3">
                <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5">Save card</button>
                <a href="{{ route('profile.edit') }}" class="text-slate-500 hover:text-slate-700">Back to profile</a>
            </div>
        </form>
    </div>
@endsection
