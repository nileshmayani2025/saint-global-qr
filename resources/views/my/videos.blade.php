@extends('layouts.consumer')
@section('title', 'Videos')

@section('content')
    {{-- Capped width: this is a phone-first shell, and an unbounded 16/9 player
         becomes absurdly tall when the app is opened in a desktop browser. --}}
    <div class="max-w-xl mx-auto">
    <h1 class="font-display font-bold text-xl mb-1">Product Videos</h1>
    <p class="text-sm text-[var(--muted)] mb-5">Learn how to use Saint Globle products correctly.</p>

    <div class="space-y-5">
        @forelse ($videos as $video)
            @php $embed = $video->embedUrl(); @endphp
            <div class="lux-card overflow-hidden">
                @if ($embed)
                    {{-- Loaded lazily: a page of autoloading iframes is heavy on
                         a phone, so each player only mounts when tapped. --}}
                    <div x-data="{ playing: false }" class="relative w-full bg-black" style="aspect-ratio:16/9">
                        <template x-if="playing">
                            <iframe src="{{ $embed }}&autoplay=1" class="absolute inset-0 w-full h-full" style="border:0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen
                                    title="{{ $video->displayTitle() }}"></iframe>
                        </template>
                        <button x-show="!playing" @click="playing = true" type="button"
                                class="absolute inset-0 w-full h-full grid place-items-center group"
                                style="background:linear-gradient(150deg,#2ca0d4,#1b5a7c)"
                                aria-label="Play {{ $video->displayTitle() }}">
                            <span class="w-16 h-16 rounded-full bg-white/90 grid place-items-center shadow-xl group-active:scale-95 transition">
                                <svg class="w-7 h-7 ml-1 text-brand-700" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </span>
                        </button>
                    </div>
                @elseif ($video->isDirectFile())
                    <video controls playsinline preload="none" class="w-full bg-black" style="aspect-ratio:16/9">
                        <source src="{{ $video->url }}">
                    </video>
                @endif

                <div class="p-4">
                    <h2 class="font-semibold leading-tight">{{ $video->displayTitle() }}</h2>
                    @if ($video->product)
                        <p class="mt-0.5 text-xs text-[var(--muted)]">{{ $video->product->name }}</p>
                    @endif
                    @if ($video->description)
                        <p class="mt-2 text-sm text-[var(--muted)]">{{ $video->description }}</p>
                    @endif

                    @unless ($embed || $video->isDirectFile())
                        <a href="{{ $video->url }}" target="_blank" rel="noopener"
                           class="mt-3 inline-block rounded-lg lux-btn text-white text-sm font-medium px-4 py-2">Watch video</a>
                    @endunless
                </div>
            </div>
        @empty
            <div class="lux-card p-10 text-center">
                <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.75 11.17l-3.2-2.13A1 1 0 0010 9.87v4.26a1 1 0 001.55.83l3.2-2.13a1 1 0 000-1.66zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="mt-3 text-slate-400">No videos yet. Check back soon.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $videos->links() }}</div>
    </div>
@endsection
