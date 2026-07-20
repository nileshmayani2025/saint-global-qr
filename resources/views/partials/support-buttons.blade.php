{{--
    Floating helpline + WhatsApp buttons, shown at the bottom of every page.

    Renders nothing when neither number is configured, so an unset .env just
    means no buttons rather than dead links.

    $supportOffset lets a layout push the stack up — the consumer app has a
    fixed bottom nav that would otherwise sit on top of these.
--}}
@php
    // Admin-set values win; .env stays the fallback default.
    $helpline = trim((string) \App\Support\Settings::get('support.helpline', config('support.helpline')));
    $whatsapp = trim((string) \App\Support\Settings::get('support.whatsapp', config('support.whatsapp')));

    // tel: and wa.me want digits only; wa.me additionally wants no leading +.
    $helplineDigits = preg_replace('/[^0-9+]/', '', $helpline);
    $whatsappDigits = preg_replace('/[^0-9]/', '', $whatsapp);
    $whatsappText = rawurlencode((string) \App\Support\Settings::get('support.whatsapp_message', config('support.whatsapp_message')));

    $offset = $supportOffset ?? 'bottom-5';
@endphp

@if ($helpline !== '' || $whatsapp !== '')
    <div class="fixed {{ $offset }} right-4 z-40 flex flex-col items-end gap-3 print:hidden">
        @if ($whatsapp !== '')
            <a href="https://wa.me/{{ $whatsappDigits }}?text={{ $whatsappText }}"
               target="_blank" rel="noopener"
               title="Chat on WhatsApp" aria-label="Chat on WhatsApp"
               class="group flex items-center gap-2.5 rounded-full bg-[#25D366] text-white shadow-lg hover:shadow-xl
                      w-12 h-12 hover:w-auto hover:pl-4 hover:pr-5 justify-center transition-all duration-200">
                <svg class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M17.47 14.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.29-.76.95-.94 1.15-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.65-2.05-.17-.29-.02-.45.13-.6.14-.13.3-.35.44-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.6-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.29-1.04 1.01-1.04 2.47s1.06 2.86 1.21 3.06c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.7.63.71.23 1.36.19 1.87.12.57-.09 1.75-.72 2-1.41.25-.69.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35z"/>
                    <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.86 9.86 0 004.79 1.22h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0012.04 2zm0 18.13h-.01a8.2 8.2 0 01-4.18-1.15l-.3-.18-3.11.82.83-3.04-.2-.31a8.17 8.17 0 01-1.26-4.36c0-4.54 3.7-8.23 8.24-8.23a8.2 8.2 0 018.23 8.24c0 4.54-3.7 8.21-8.24 8.21z"/>
                </svg>
                <span class="hidden group-hover:inline whitespace-nowrap text-sm font-semibold">WhatsApp</span>
            </a>
        @endif

        @if ($helpline !== '')
            <a href="tel:{{ $helplineDigits }}"
               title="Call {{ $helpline }}" aria-label="Call helpline {{ $helpline }}"
               class="group flex items-center gap-2.5 rounded-full bg-brand-600 text-white shadow-lg hover:shadow-xl hover:bg-brand-700
                      w-12 h-12 hover:w-auto hover:pl-4 hover:pr-5 justify-center transition-all duration-200">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11 11 0 005.5 5.5l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"/>
                </svg>
                <span class="hidden group-hover:inline whitespace-nowrap text-sm font-semibold">{{ $helpline }}</span>
            </a>
        @endif
    </div>
@endif
