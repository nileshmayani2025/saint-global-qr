{{--
    Toast host. Everything that used to render as an inline flash banner now
    surfaces here, and incoming foreground push notifications reuse the same
    stack so an arriving message looks identical however it was triggered.

    Fire one from anywhere:  window.toast('Saved.', 'success')
--}}
<div x-data="toastHost()" x-on:toast.window="push($event.detail)"
     class="fixed z-[100] top-4 right-4 left-4 sm:left-auto sm:w-96 space-y-2.5 pointer-events-none"
     aria-live="polite" aria-atomic="true">
    <template x-for="item in items" :key="item.id">
        <div x-show="item.visible" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-x-4"
             class="lux-card pointer-events-auto flex items-start gap-3 p-4 shadow-lg border-l-4"
             :class="{
                'border-l-emerald-500': item.type === 'success',
                'border-l-rose-500': item.type === 'error',
                'border-l-amber-500': item.type === 'warning',
                'border-l-brand-500': item.type === 'info',
             }">
            <div class="mt-0.5 shrink-0"
                 :class="{
                    'text-emerald-500': item.type === 'success',
                    'text-rose-500': item.type === 'error',
                    'text-amber-500': item.type === 'warning',
                    'text-brand-500': item.type === 'info',
                 }">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path x-show="item.type === 'success'" stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    <path x-show="item.type === 'error'" stroke-linecap="round" stroke-linejoin="round" d="M10 14l4-4m0 4l-4-4m11 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    <path x-show="item.type === 'warning'" stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <path x-show="item.type === 'info'" stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <div class="flex-1 min-w-0">
                <p x-show="item.title" x-text="item.title" class="text-sm font-semibold leading-snug"></p>
                <p x-text="item.message" class="text-sm text-[var(--muted)] leading-snug break-words"></p>
                <template x-if="item.url">
                    <a :href="item.url" class="mt-1.5 inline-block text-xs font-medium text-brand-500 hover:underline">View</a>
                </template>
            </div>

            <button type="button" @click="dismiss(item.id)" aria-label="Dismiss"
                    class="shrink-0 text-[var(--muted)] hover:text-[var(--text)] transition">&times;</button>
        </div>
    </template>
</div>

@push('scripts')
    <script>
        function toastHost() {
            return {
                items: [],
                seq: 0,
                push(detail) {
                    var item = {
                        id: ++this.seq,
                        type: detail.type || 'info',
                        title: detail.title || '',
                        message: detail.message || '',
                        url: detail.url || '',
                        visible: true,
                    };
                    this.items.push(item);

                    // Errors stay until dismissed; everything else self-clears.
                    if (item.type !== 'error') {
                        setTimeout(() => this.dismiss(item.id), detail.duration || 6000);
                    }
                },
                dismiss(id) {
                    var item = this.items.find(i => i.id === id);
                    if (!item) return;
                    item.visible = false;
                    setTimeout(() => { this.items = this.items.filter(i => i.id !== id); }, 200);
                },
            };
        }

        // Global helper — usable from any inline script or console.
        window.toast = function (message, type, extra) {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: Object.assign({ message: message, type: type || 'info' }, extra || {}),
            }));
        };

        // Server-side flash messages, replayed as toasts once Alpine is ready.
        document.addEventListener('alpine:initialized', function () {
            @foreach (['success', 'error', 'warning', 'info'] as $level)
                @if (session($level))
                    window.toast(@json(session($level)), '{{ $level }}');
                @endif
            @endforeach
        });
    </script>
@endpush
