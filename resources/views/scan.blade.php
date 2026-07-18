@extends(auth()->user()->isConsumer() ? 'layouts.consumer' : 'layouts.app')
@section('title', 'Scan QR')

@section('content')
    <div class="max-w-md mx-auto">
        <div class="lux-card p-5">
            <h2 class="font-semibold mb-1">Scan product QR</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Allow camera access and hold the QR label inside the frame.</p>

            <div id="reader" class="rounded-xl overflow-hidden bg-black/5 dark:bg-white/5"></div>

            <p id="scan-status" class="text-sm text-slate-500 dark:text-slate-400 mt-3 text-center">Starting camera…</p>

            <div class="mt-5 pt-5 border-t border-[var(--border)]">
                <label class="block text-sm mb-1">Or enter the code manually</label>
                <form method="POST" action="{{ route('verify.submit') }}" class="flex gap-2">
                    @csrf
                    <input name="code" required placeholder="Product code"
                           class="flex-1 lux-field px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500">
                    <button class="rounded-lg lux-btn text-white font-medium px-4">Go</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        (function () {
            var VERIFY_BASE = @js(url('verify'));
            var status = document.getElementById('scan-status');
            var handled = false;

            function goToCode(text) {
                if (handled) return;
                handled = true;
                status.textContent = 'Code detected — verifying…';

                text = (text || '').trim();

                // QR labels encode a full verify URL, but older batches baked a
                // different host / base path (e.g. a dev machine's IP) that 404s
                // on a phone. Pull the code out of any ".../verify/<code>" and
                // re-point it at THIS site so every label resolves here; anything
                // else is treated as a raw product code.
                var m = text.match(/\/verify\/([^\/?#\s]+)/i);
                if (m) {
                    window.location.href = VERIFY_BASE + '/' + encodeURIComponent(decodeURIComponent(m[1]));
                } else if (/^https?:\/\//i.test(text)) {
                    // An unrelated URL we don't recognise — follow it as-is.
                    window.location.href = text;
                } else {
                    window.location.href = VERIFY_BASE + '/' + encodeURIComponent(text);
                }
            }

            if (typeof Html5Qrcode === 'undefined') {
                status.textContent = 'Scanner failed to load. Use manual entry below.';
                return;
            }

            var scanner = new Html5Qrcode('reader');
            var config = { fps: 10, qrbox: { width: 240, height: 240 } };

            scanner.start({ facingMode: 'environment' }, config,
                function (decodedText) {
                    scanner.stop().then(function () { goToCode(decodedText); }).catch(function () { goToCode(decodedText); });
                },
                function () { /* per-frame decode failures are ignored */ }
            ).then(function () {
                status.textContent = 'Point the camera at a QR code.';
            }).catch(function (err) {
                status.textContent = 'Cannot access camera (' + err + '). Please allow camera permission or use manual entry.';
            });

            // Release the camera when leaving the page.
            window.addEventListener('pagehide', function () {
                try { scanner.stop(); } catch (e) {}
            });
        })();
    </script>
@endsection
