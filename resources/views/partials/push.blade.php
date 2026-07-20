{{--
    Firebase web-push registration.

    Renders nothing unless the Firebase web config is filled in, so an
    unconfigured environment simply has no push rather than a console full of
    errors. Permission is never requested on page load — the user opts in from
    the bell menu or their profile, which is both better UX and what Chrome's
    abuse heuristics expect.
--}}
@php
    $fb = array_filter(config('services.firebase.web', []));
    $pushReady = ! empty($fb['api_key']) && ! empty($fb['project_id']) && ! empty($fb['messaging_sender_id']) && ! empty($fb['vapid_key']);

    // Built here rather than inline: Blade's @json cannot parse a multi-line
    // array literal — it truncates the argument and emits broken PHP.
    $fbWebConfig = [
        'apiKey' => $fb['api_key'] ?? '',
        'authDomain' => $fb['auth_domain'] ?? '',
        'projectId' => $fb['project_id'] ?? '',
        'storageBucket' => $fb['storage_bucket'] ?? '',
        'messagingSenderId' => $fb['messaging_sender_id'] ?? '',
        'appId' => $fb['app_id'] ?? '',
    ];
@endphp

@if ($pushReady)
    @push('scripts')
        <script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js"></script>
        <script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js"></script>
        <script>
            window.SaintPush = (function () {
                var config = @json($fbWebConfig);
                var vapidKey = @json($fb['vapid_key'] ?? '');
                var swUrl = @json(route('push.service-worker'));
                var subscribeUrl = @json(route('push.subscribe'));
                var csrf = document.querySelector('meta[name="csrf-token"]').content;

                var messaging = null;
                var registration = null;

                function supported() {
                    return 'serviceWorker' in navigator && 'Notification' in window
                        && window.firebase && firebase.messaging && firebase.messaging.isSupported();
                }

                function init() {
                    if (messaging) return Promise.resolve(messaging);
                    if (!supported()) return Promise.reject(new Error('unsupported'));

                    firebase.initializeApp(config);
                    messaging = firebase.messaging();

                    // Scope the worker to the app root, which on live is the
                    // /qr/public subdirectory rather than the domain root.
                    return navigator.serviceWorker.register(swUrl, { scope: new URL(swUrl, location.href).pathname.replace(/[^/]*$/, '') })
                        .then(function (reg) {
                            registration = reg;
                            listenForeground();
                            return messaging;
                        });
                }

                // A message arriving while the tab is focused does NOT raise an
                // OS notification, so show it as a toast instead.
                function listenForeground() {
                    messaging.onMessage(function (payload) {
                        var n = payload.notification || {};
                        var d = payload.data || {};
                        window.toast(n.body || '', 'info', { title: n.title || '', url: d.url || '', duration: 10000 });
                        bumpBell();
                    });
                }

                function bumpBell() {
                    window.dispatchEvent(new CustomEvent('notification-received'));
                }

                function sendToken(token) {
                    return fetch(subscribeUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ token: token }),
                    });
                }

                /** Ask for permission and register this device. Returns a promise. */
                function enable() {
                    return init()
                        .then(function () { return Notification.requestPermission(); })
                        .then(function (permission) {
                            if (permission !== 'granted') {
                                throw new Error('denied');
                            }
                            return messaging.getToken({ vapidKey: vapidKey, serviceWorkerRegistration: registration });
                        })
                        .then(function (token) {
                            if (!token) throw new Error('no-token');
                            return sendToken(token);
                        });
                }

                /** Silently refresh the stored token for an already-opted-in user. */
                function refresh() {
                    if (!supported() || Notification.permission !== 'granted') return;

                    init()
                        .then(function () {
                            return messaging.getToken({ vapidKey: vapidKey, serviceWorkerRegistration: registration });
                        })
                        .then(function (token) { if (token) sendToken(token); })
                        .catch(function () { /* nothing useful to tell the user here */ });
                }

                document.addEventListener('DOMContentLoaded', refresh);

                return { enable: enable, refresh: refresh, supported: supported, permission: function () { return window.Notification ? Notification.permission : 'unsupported'; } };
            })();

            /** Wired to the "Enable notifications" buttons. */
            function enablePushNotifications(button) {
                if (!window.SaintPush || !window.SaintPush.supported()) {
                    window.toast('This browser does not support push notifications.', 'warning');
                    return;
                }

                button && (button.disabled = true);

                window.SaintPush.enable()
                    .then(function () {
                        window.toast('Notifications are on for this device.', 'success');
                        button && button.remove();
                    })
                    .catch(function (e) {
                        button && (button.disabled = false);
                        window.toast(
                            e.message === 'denied'
                                ? 'Notifications are blocked. Allow them in your browser settings to turn them on.'
                                : 'Could not enable notifications. Please try again.',
                            'error'
                        );
                    });
            }
        </script>
    @endpush
@endif
