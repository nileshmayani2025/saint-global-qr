{{--
    Welcome video popup for the consumer app.

    Shows the newest active trading video the moment the app opens, ahead of the
    home screen, and does not come back until a newer video is published — the
    user's last_seen_trading_video_id is the watermark.

    Expects $welcomeVideo (App\Models\ProductTradingVideo|null).
--}}
@if ($welcomeVideo)
    @php
        $embed = $welcomeVideo->embedUrl();
    @endphp

    <div x-data="tradingVideoPopup({{ $welcomeVideo->id }}, @js(route('my.trading-video.seen', $welcomeVideo)))"
         x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[90] flex items-end sm:items-center justify-center p-0 sm:p-4"
         role="dialog" aria-modal="true" aria-label="Product video">

        <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" @click="dismiss()"></div>

        <div class="lux-card lux-pop relative w-full sm:max-w-lg rounded-b-none sm:rounded-2xl overflow-hidden"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="translate-y-6 sm:scale-95 opacity-0"
             x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100">

            <div class="flex items-start justify-between gap-3 px-5 pt-4 pb-3">
                <div class="min-w-0">
                    <h2 class="font-display font-bold text-lg leading-tight truncate">{{ $welcomeVideo->displayTitle() }}</h2>
                    @if ($welcomeVideo->product)
                        <p class="text-xs text-[var(--muted)] truncate">{{ $welcomeVideo->product->name }}</p>
                    @endif
                </div>
                <button type="button" @click="dismiss()" aria-label="Close"
                        class="shrink-0 w-8 h-8 grid place-items-center rounded-lg text-[var(--muted)] hover:bg-black/5 dark:hover:bg-white/10 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @if ($embed)
                <div class="relative w-full" style="aspect-ratio:16/9">
                    {{-- Only loaded once the popup is open so a hidden iframe never
                         costs the user bandwidth on every home-screen visit. --}}
                    <template x-if="open">
                        <iframe src="{{ $embed }}" class="absolute inset-0 w-full h-full" style="border:0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen title="{{ $welcomeVideo->displayTitle() }}"></iframe>
                    </template>
                </div>
            @elseif ($welcomeVideo->isDirectFile())
                <video controls playsinline class="w-full bg-black" style="aspect-ratio:16/9">
                    <source src="{{ $welcomeVideo->url }}">
                </video>
            @else
                {{-- Unknown host: link out rather than risk an iframe the site blocks. --}}
                <div class="px-5 py-8 text-center">
                    <a href="{{ $welcomeVideo->url }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-lg lux-btn text-white font-medium px-5 py-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Watch video
                    </a>
                </div>
            @endif

            @if ($welcomeVideo->description)
                <p class="px-5 pt-3 text-sm text-[var(--muted)]">{{ $welcomeVideo->description }}</p>
            @endif

            <div class="px-5 py-4">
                <button type="button" @click="dismiss()"
                        class="w-full rounded-lg lux-ghost py-2.5 text-sm font-medium">
                    Continue to app
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function tradingVideoPopup(videoId, seenUrl) {
                return {
                    open: true,
                    dismiss() {
                        this.open = false;
                        // Fire-and-forget: if this fails the popup simply shows
                        // again next time, which is the safe direction to fail.
                        fetch(seenUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        }).catch(() => {});
                    },
                };
            }
        </script>
    @endpush
@endif
