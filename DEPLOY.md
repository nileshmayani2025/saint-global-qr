# Saint Globle — Live deployment (production build)

The app now uses **compiled assets** instead of the Tailwind Play CDN, so the
browser no longer compiles CSS on every page load. Follow these steps on the
live server to build and cache everything for full speed.

`/vendor`, `/node_modules`, `/public/build` and `.env` are git-ignored, so they
are built on the server — they are **not** shipped in the repo.

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

# Frontend assets → public/build
npm ci
npm run build

# DB
php artisan migrate --force

# One-time: public storage symlink (app already has a /media fallback route)
php artisan storage:link

# Cache config, routes, views + optimize
php artisan optimize
```

To redeploy after code changes: `git pull` → repeat `npm run build` →
`php artisan optimize:clear && php artisan optimize`.

## 3. Make sure PHP OPcache is on (biggest speed win on shared hosting)

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
