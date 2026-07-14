# Saint Global Reward & Product Verification ERP

A production-grade Laravel 12 ERP for QR-based product authentication, anti-counterfeit
verification, and reward distribution. Built with a server-rendered premium UI
(Blade + Tailwind v4 + Alpine.js) and a versioned REST API for the mobile app.

> **Status:** Module 1 — **Foundation + Auth/RBAC + Product Catalog + QR Engine +
> Verification Engine** — is complete, tested (26 passing tests) and runnable.
> See [Roadmap](#roadmap) for the remaining modules.

---

## Tech stack

| Layer      | Technology                                              |
|------------|---------------------------------------------------------|
| Backend    | Laravel 12, PHP 8.2 (XAMPP), Repository + Service layers |
| Database   | MariaDB 10.4 (MySQL-compatible), InnoDB, utf8mb4         |
| Auth / RBAC| Laravel session auth + `spatie/laravel-permission`      |
| QR         | `endroid/qr-code` v6 (GD), HMAC-signed tokens           |
| Frontend   | Blade, TailwindCSS v4, Alpine.js, Vite                  |
| API        | Versioned REST (`/api/v1`), rate limited                |

## What's built

- **Audit-ready foundation** — every table carries `uuid`, `created_by/updated_by/deleted_by`,
  soft deletes; a universal `activity_logs` table captures IP / browser / device / URL on
  every model change via observers. Reusable `auditColumns()` / `publicId()` schema macros.
- **RBAC** — 13 roles, 56 permissions, a single-source-of-truth catalogue
  (`app/Support/Access/AccessControl.php`), super-admin gate bypass, policies per model.
- **Product catalog** — Company → Brand / Category → Product → Batch, full CRUD with
  full-page forms, searchable Select2-style dropdowns, filters, pagination, slug generation.
- **QR engine** — secure random tokens, HMAC signatures, PNG generation to the public disk,
  synchronous + queued (`GenerateBatchQrCodesJob`) generation, printable QR sheets.
- **Verification engine** — public web page + JSON API. Detects genuine / duplicate /
  counterfeit / blocked / expired, with a fraud service (blacklist, geofence, device
  scan-velocity) and a race-safe first-scan-wins guarantee.
- **Premium UI** — collapsible sidebar, dark/light mode, global command palette (⌘K),
  toasts, skeleton loaders, glassmorphism, interactive dashboard charts.

## Requirements

- PHP 8.2+ with `gd`, `zip`, `pdo_mysql`, `mbstring`, `openssl` (all enabled in this XAMPP)
- Composer 2.x
- MariaDB 10.4+ / MySQL 8
- Node 20+ / npm

## Setup

```bash
# 1. Install PHP + JS dependencies
composer install
npm install

# 2. Environment (already configured for MariaDB in .env)
php artisan key:generate        # only if APP_KEY is empty

# 3. Create the database, then migrate + seed
#    (DB name: saint_global — see .env)
php artisan migrate:fresh --seed

# 4. Link storage (serves QR images) + build assets
php artisan storage:link
npm run build

# 5. Serve
php artisan serve
```

> On this machine PHP/Composer/MySQL live under XAMPP; call them with full paths, e.g.
> `E:/xampp/php/php.exe artisan …` and `E:/xampp/mysql/bin/mysql.exe`.

### Demo credentials

| Role        | Email                 | Password   |
|-------------|-----------------------|------------|
| Super Admin | admin@test    | `password` |
| Company     | company@test  | `password` |

The seeder creates a company, brands, categories, 3 products, 3 batches and **30 genuine
QR codes** you can immediately verify.

## Key routes

| URL                         | Purpose                                    |
|-----------------------------|--------------------------------------------|
| `/login`                    | Admin sign-in                              |
| `/dashboard`                | Stats, charts, recent activity             |
| `/products`, `/brands`, `/categories` | Catalog management               |
| `/batches` → generate QR    | Batch management + QR generation           |
| `/qr-codes`                 | Browse / print / block QR codes            |
| `/verify`                   | **Public** product verification page       |
| `/verify/{code}`            | QR-scan landing (encoded in every QR)      |
| `POST /api/v1/verify`       | JSON verification (mobile app / partners)  |

## Testing

```bash
php artisan test          # 26 tests, 65 assertions — uses the saint_global_test DB
```

Covers the QR signature, authentication, RBAC, product CRUD + soft-delete blame, and the
full verification engine (genuine / duplicate / invalid / blocked / expired / blacklisted)
over both the service layer and HTTP.

## Architecture

See [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) and [`docs/DATABASE.md`](docs/DATABASE.md).

Request flow per module: **Migration → Model (+Observer) → Repository → Service →
FormRequest → Policy → Controller → Blade/API Resource → Seeder → Tests.**

## Roadmap

Module 1 (this release) establishes the platform. Remaining modules build on the same
conventions:

2. **Wallet** — reward & cashback wallets, ledger, statements
3. **Reward engine** — dynamic rules, campaigns, milestones, target rewards
4. **Redemption** — UPI / bank transfer / gift, lucky draw, spin wheel, scratch card
5. **KYC & onboarding** — dealer / distributor / Saint Global / retailer
6. **Notifications** — SMS / WhatsApp / email / push (queued)
7. **Reports & exports** — Excel / PDF, financial / reward / QR / campaign reports
8. **Settings** — company, theme, language, currency, backup
9. **API expansion** — Sanctum tokens, Swagger docs, API logs
10. **Capacitor mobile app** — offline scan, camera, GPS, push
