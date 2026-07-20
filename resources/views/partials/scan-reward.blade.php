{{--
    The reward celebration after a successful scan, in the shape people already
    know from payment apps: a ring fills, a tick lands, then a scratch card
    hides the points until the user rubs it off.

    The verification itself already happened server-side — this sequence is
    presentation, not work in progress, which is why it runs on a fixed timeline
    rather than waiting on anything.

    Expects: $points (int), $productName (string|null).
--}}
@once
    @push('scripts')
        <style>
            @keyframes sr-draw-ring { to { stroke-dashoffset: 0; } }
            @keyframes sr-draw-tick { to { stroke-dashoffset: 0; } }
            @keyframes sr-spin      { to { transform: rotate(360deg); } }
            @keyframes sr-pop       { 0% { transform: scale(.7); opacity: 0 } 60% { transform: scale(1.06) } 100% { transform: scale(1); opacity: 1 } }
            @keyframes sr-rise      { from { transform: translateY(14px); opacity: 0 } to { transform: translateY(0); opacity: 1 } }

            .sr-spinner   { animation: sr-spin 1s linear infinite; transform-origin: 50% 50%; }
            .sr-ring-fill { stroke-dasharray: 302; stroke-dashoffset: 302; animation: sr-draw-ring .55s ease-out forwards; }
            .sr-tick      { stroke-dasharray: 48; stroke-dashoffset: 48; animation: sr-draw-tick .4s .35s ease-out forwards; }
            .sr-pop       { animation: sr-pop .45s cubic-bezier(.2,.8,.3,1) forwards; }
            .sr-rise      { animation: sr-rise .4s ease-out both; }

            /* The scratch surface must own the gesture, or the page pans instead. */
            .sr-canvas { touch-action: none; cursor: grab; }

            @media (prefers-reduced-motion: reduce) {
                .sr-spinner, .sr-ring-fill, .sr-tick, .sr-pop, .sr-rise { animation: none !important; stroke-dashoffset: 0 !important; opacity: 1 !important; }
            }
        </style>

        <script>
            function scanReward(points) {
                return {
                    phase: 'checking',   // checking → done → card
                    points: points,
                    revealed: false,

                    start() {
                        // Respect a reduced-motion preference by skipping straight
                        // to the card rather than forcing the sequence.
                        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                            this.phase = 'card';
                            this.$nextTick(() => this.initScratch());
                            return;
                        }
                        setTimeout(() => { this.phase = 'done'; }, 1100);
                        setTimeout(() => { this.phase = 'card'; this.$nextTick(() => this.initScratch()); }, 2300);
                    },

                    initScratch() {
                        const canvas = this.$refs.scratch;
                        if (!canvas || canvas.dataset.ready) return;
                        canvas.dataset.ready = '1';

                        const rect = canvas.getBoundingClientRect();
                        const dpr = window.devicePixelRatio || 1;
                        canvas.width = rect.width * dpr;
                        canvas.height = rect.height * dpr;
                        const ctx = canvas.getContext('2d');
                        ctx.scale(dpr, dpr);

                        const grad = ctx.createLinearGradient(0, 0, rect.width, rect.height);
                        grad.addColorStop(0, '#2ca0d4');
                        grad.addColorStop(1, '#1b5a7c');
                        ctx.fillStyle = grad;
                        ctx.fillRect(0, 0, rect.width, rect.height);

                        ctx.fillStyle = 'rgba(255,255,255,.92)';
                        ctx.font = '600 15px system-ui, sans-serif';
                        ctx.textAlign = 'center';
                        ctx.fillText('Scratch here', rect.width / 2, rect.height / 2 + 5);

                        ctx.globalCompositeOperation = 'destination-out';

                        let drawing = false;
                        const scratch = (e) => {
                            const r = canvas.getBoundingClientRect();
                            const p = e.touches ? e.touches[0] : e;
                            ctx.beginPath();
                            ctx.arc(p.clientX - r.left, p.clientY - r.top, 22, 0, Math.PI * 2);
                            ctx.fill();
                        };

                        const down = (e) => { drawing = true; scratch(e); };
                        const move = (e) => { if (drawing) { e.preventDefault(); scratch(e); } };
                        const up = () => { if (!drawing) return; drawing = false; this.checkCleared(ctx, rect); };

                        canvas.addEventListener('pointerdown', down);
                        canvas.addEventListener('pointermove', move);
                        window.addEventListener('pointerup', up);
                    },

                    /** Past roughly a third scratched, finish it for them. */
                    checkCleared(ctx, rect) {
                        const data = ctx.getImageData(0, 0, rect.width, rect.height).data;
                        let clear = 0;
                        for (let i = 3; i < data.length; i += 4 * 24) {
                            if (data[i] === 0) clear++;
                        }
                        if (clear / (data.length / (4 * 24)) > 0.33) this.revealAll();
                    },

                    revealAll() {
                        if (this.revealed) return;
                        this.revealed = true;
                        const canvas = this.$refs.scratch;
                        if (canvas) {
                            canvas.style.transition = 'opacity .4s';
                            canvas.style.opacity = '0';
                        }
                    },
                };
            }
        </script>
    @endpush
@endonce

<div x-data="scanReward({{ $points }})" x-init="start()" class="max-w-sm mx-auto text-center py-6">

    {{-- Ring: spinning while "checking", filled with a tick once done --}}
    <div x-show="phase !== 'card'" class="py-8">
        {{-- x-show, not <template x-if>: a <template> inside <svg> has its
             contents parsed in the HTML namespace, so cloned <circle>/<path>
             nodes are not SVG elements and never paint. x-show only toggles
             `display`, which SVG honours. The animation classes are bound
             rather than static so they start when the phase flips, not while
             the element is still hidden. --}}
        <svg viewBox="0 0 120 120" class="w-32 h-32 mx-auto" fill="none">
            <circle cx="60" cy="60" r="48" stroke="currentColor" stroke-width="6"
                    class="text-slate-200 dark:text-slate-700"/>

            <circle x-show="phase === 'checking'" cx="60" cy="60" r="48" stroke="#2ca0d4" stroke-width="6"
                    stroke-linecap="round" stroke-dasharray="70 232" class="sr-spinner"/>

            <circle x-show="phase === 'done'" x-cloak cx="60" cy="60" r="48" stroke="#16a34a" stroke-width="6"
                    stroke-linecap="round" transform="rotate(-90 60 60)"
                    :class="{ 'sr-ring-fill': phase === 'done' }"/>

            <path x-show="phase === 'done'" x-cloak d="M40 62 L54 76 L81 47" stroke="#16a34a" stroke-width="7"
                  stroke-linecap="round" stroke-linejoin="round"
                  :class="{ 'sr-tick': phase === 'done' }"/>
        </svg>

        <p class="mt-5 font-display font-bold text-lg" x-text="phase === 'checking' ? 'Verifying…' : 'Verified!'"></p>
        <p class="mt-1 text-sm text-[var(--muted)]"
           x-text="phase === 'checking' ? 'Checking this code' : 'Genuine Saint Globle product'"></p>
    </div>

    {{-- Scratch card --}}
    <div x-show="phase === 'card'" x-cloak class="sr-rise">
        <p class="font-display font-bold text-lg">You earned a reward!</p>
        <p class="mt-1 text-sm text-[var(--muted)]"
           x-text="revealed ? 'Added to your rewards balance.' : 'Scratch the card to see your points.'"></p>

        <div class="relative mt-5 mx-auto w-full max-w-[280px] aspect-[4/3] rounded-2xl overflow-hidden shadow-lg
                    ring-1 ring-black/5 dark:ring-white/10">
            {{-- Prize underneath --}}
            <div class="absolute inset-0 grid place-items-center bg-white dark:bg-[#0e1a2b]">
                <div class="sr-pop">
                    <div class="text-4xl font-display font-extrabold text-amber-500">+{{ $points }}</div>
                    <div class="mt-1 text-sm font-semibold text-[var(--muted)]">reward point{{ $points === 1 ? '' : 's' }}</div>
                    @if ($productName)
                        <div class="mt-2 text-xs text-[var(--muted)] px-4">{{ $productName }}</div>
                    @endif
                </div>
            </div>

            {{-- Scratch surface on top --}}
            <canvas x-ref="scratch" class="sr-canvas absolute inset-0 w-full h-full"
                    aria-label="Scratch card covering your reward"></canvas>
        </div>

        {{-- Keyboard / assistive path — scratching needs a pointer. --}}
        <button type="button" @click="revealAll()" x-show="!revealed"
                class="mt-4 text-sm font-medium text-brand-500 hover:underline">
            Can't scratch? Reveal
        </button>

        <div x-show="revealed" x-cloak class="mt-5 flex flex-col gap-2">
            <a href="{{ route('my.rewards') }}" class="rounded-lg lux-btn text-white font-medium py-2.5">See my rewards</a>
            <a href="{{ route('scan') }}" class="rounded-lg lux-ghost font-medium py-2.5">Scan another</a>
        </div>
    </div>
</div>
