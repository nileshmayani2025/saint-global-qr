# Saint Globle — Live deployment (production build)

The app now uses **compiled assets** instead of the Tailwind Play CDN, so the
browser no longer compiles CSS on every page load. Follow these steps on the
live server to build and cache everything for full speed.

`/vendor`, `/node_modules` and `.env` are git-ignored (built/set on the server).
**`/public/build` IS committed** — the shared cPanel host has no Node, so the
compiled CSS/JS ship in the repo. Whenever you change CSS/JS, run `npm run build`
locally and commit the new `public/build/` output.

## 1. Live `.env` (once)

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com/qr/public   # must match the real sub-path
```

Everything else (DB, mail, etc.) stays as your live values.

## 2. Build & deploy

```bash
# PHP dependencies (no dev tools)
composer install --no-dev --optimize-autoloader

# Frontend assets already committed in public/build (no Node needed on server).
# Only rebuild if you have Node available: npm ci && npm run build

# DB
php artisan migrate --force

# One-time: public storage symlink (app already has a /media fallback route)
php artisan storage:link

# Cache config, routes, views + optimize
php artisan optimize
```

To redeploy after code changes: `git pull` → repeat `npm run build` →
`php artisan optimize:clear && php artisan optimize`.

> **Always run `optimize:clear` BEFORE `optimize`.** Running `optimize` on its
> own leaves the previous `bootstrap/cache/routes-v7.php` in place, and a route
> cache built from older code is what produces errors like
> *"The GET method is not supported for route /. Supported methods: HEAD."* —
> the compiled route table no longer matches `routes/web.php`.

### After running migrations that add permissions

New modules register their permissions through `AccessControl::catalogue()`, so
re-run the (idempotent) seeder or nobody but the super-admin can see them:

```bash
php artisan db:seed --class=RolePermissionSeeder --force
php artisan db:seed --class=GeographySeeder --force   # India country/state/city origin data
```

## Troubleshooting: 405 "Method Not Allowed" on a page that used to work

Almost always a stale compiled cache rather than a code bug. On the live server:

```bash
php artisan optimize:clear
php artisan optimize
```

If it persists, PHP's OPcache is still serving the old compiled cache files from
memory. Restart PHP-FPM (or the account's PHP process in cPanel), or add
`opcache.validate_timestamps=1` so changed files are picked up automatically.

## 3. Firebase push notifications (one-time setup)

Push is optional — without it, notifications still save and show in each user's
in-app bell, they just never reach the device. To turn on real push:

1. Create a project at <https://console.firebase.google.com>.
2. **Project settings → General → Your apps → Add app → Web.** Copy the config
   values into `.env` as `FIREBASE_WEB_*` (these are public, not secrets).
3. **Project settings → Cloud Messaging → Web configuration → Web Push
   certificates → Generate key pair.** Copy it to `FIREBASE_WEB_VAPID_KEY`.
4. **Project settings → Service accounts → Generate new private key.** Upload
   the JSON to the server *outside* the web root, e.g.:

   ```bash
   mkdir -p storage/app/firebase
   # upload the file as storage/app/firebase/service-account.json
   chmod 600 storage/app/firebase/service-account.json
   ```

   Then set `FIREBASE_PROJECT_ID` and, if you used a different location,
   `FIREBASE_CREDENTIALS`. **Never commit this file** — it is a private key and
   is already listed in `.gitignore`.
5. `php artisan config:clear && php artisan config:cache`

**Push requires HTTPS.** The service worker is served by Laravel at
`/firebase-messaging-sw.js` (so on live: `/qr/public/firebase-messaging-sw.js`),
which scopes it to the app's subdirectory automatically.

### The queue must be running

Sending fans out one FCM call per device, so it runs on the queue
(`QUEUE_CONNECTION=database`). Without a worker, campaigns sit at **queued**
forever. On cPanel, add a cron entry:

```
* * * * * cd /home/USER/path-to-app && php artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

### Permissions for the new module

```bash
php artisan db:seed --class=RolePermissionSeeder --force
```

Grants `notifications.view|create|update|delete|send`. Note `send` is separate
from `create`, so you can let staff draft campaigns without letting them
broadcast.

## 4. Make sure PHP OPcache is on (biggest speed win on shared hosting)

In `php.ini`:

```
opcache.enable=1
opcache.jit=1255
opcache.jit_buffer_size=64M
```

## Why it was slow before

- The pages loaded `https://cdn.tailwindcss.com`, which **compiles Tailwind in
  the browser on every request** (it even prints a "not for production" warning)
  and depends on an outbound internet call per page load. That is now a single
  pre-built ~7 kB (gzip) CSS file served from your own server.
- The built-in `php artisan serve` is single-threaded (~4–9 s/request on this
  box). Live runs under Apache with OPcache + cached config/routes/views, which
  is dramatically faster.
