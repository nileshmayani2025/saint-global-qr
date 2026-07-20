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

# Cache config, events and views — but NOT routes. See the warning below.
php artisan optimize --except=routes
```

To redeploy after code changes: `git pull` → repeat `npm run build` →
`php artisan optimize:clear && php artisan optimize --except=routes`.

> ### ⚠️ Never cache routes while the app lives in a subdirectory
>
> This install is served from `/qr/public`, not from a domain root. Caching
> routes in that setup **breaks the home page** with:
>
> *"The GET method is not supported for route /. Supported methods: HEAD."*
>
> It is not a stale cache — a freshly built one does it too. `CompiledRouteCollection`
> strips the trailing slash from the request before matching, so `/qr/public/`
> becomes `/qr/public`, which is exactly the base path. Symfony then reads the
> base path as empty and looks for a route literally named `qr/public`, finds
> nothing, and the fallback reports `Allow: HEAD`. Only the `/` route is
> affected; every other URL still resolves.
>
> `--except=routes` keeps the config, event and view caches (nearly all of the
> speed) and skips the one that breaks. Reproduced and verified both ways.
>
> The real cure is to stop serving from a subdirectory: point the domain or a
> subdomain's document root straight at `public/`. Route caching is then safe
> and you can drop the flag.

### After running migrations that add permissions

New modules add entries to `AccessControl::catalogue()`. Until those rows exist
the Roles screen cannot list them, so nobody can be granted the new modules:

```bash
php artisan permissions:sync            # adds missing permissions, grants nothing
php artisan db:seed --class=GeographySeeder --force   # India country/state/city origin data
```

`permissions:sync` is purely additive and safe on a live site. The Roles screen
also detects the gap and offers the same action as a button, so shell access is
not required. Add `--grant` only on a fresh install that wants the default
role → permission map.

**Do not run `RolePermissionSeeder` on a live site** — it calls
`syncPermissions()`, which resets every role to its defaults and discards any
tuning done through the Roles screen.

## Troubleshooting

### 405 "The GET method is not supported for route /"

The route cache. See the warning above — it is not staleness, it is route
caching plus the `/qr/public` subdirectory. Fix:

```bash
php artisan route:clear
```

and from then on deploy with `php artisan optimize --except=routes`.

### "Route [x.index] not defined" after adding a module

A genuinely stale route cache this time: the new code is on disk but the
compiled table predates it.

```bash
php artisan optimize:clear
php artisan optimize --except=routes
```

### A new module does not appear in any menu

Its permissions have no rows yet, so nobody holds them. Open **Roles** — a
banner lists what is missing with a button to add it — or run
`php artisan permissions:sync`. Then grant them to the roles that should have
them; nothing is granted automatically.

### Changes deployed but the site still behaves as before

PHP's OPcache is serving the previous compiled files from memory. Restart
PHP-FPM (or the account's PHP process in cPanel), or set
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
