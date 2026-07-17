# Saint Globle Reward & Product Verification ERP — System Architecture

> Stack decision (confirmed): **Laravel 12 + Blade + TailwindCSS + Alpine.js**, **MariaDB 10.4 (MySQL-compatible)**, Redis-ready queues. Server-rendered premium UI. A versioned REST API (Sanctum) is exposed in parallel so the Capacitor mobile app can be added later without rework.

---

## 1. High-Level Architecture

```
┌───────────────────────────────────────────────────────────────────────┐
│                              CLIENTS                                    │
│  Web Portals (Blade)      REST API (JSON)          Capacitor App (later)│
│  Super Admin / Company /  /api/v1/*  (Sanctum)     QR scan / offline    │
│  Dealer / Distributor /                                                 │
│  Salesman / Saint Globle / User                                             │
└───────────────┬───────────────────────────────┬───────────────────────┘
                │ web (session + CSRF)           │ api (Sanctum token)
┌───────────────▼───────────────────────────────▼───────────────────────┐
│                       LARAVEL 12 APPLICATION                            │
│                                                                         │
│  Http Layer   ── Controllers (Web + Api) · FormRequests · Middleware   │
│                  (RBAC, Audit, DeviceLock, RateLimit, ForceJson)        │
│  Service Layer ─ Business logic (QrService, VerificationService,        │
│                  RewardEngine, WalletService, FraudService)             │
│  Repository ──── Data access (interface-bound, swappable)              │
│  Domain ──────── Eloquent Models · Observers · Events · Policies        │
│  Support ─────── Traits (HasUuid, Auditable, BlameableSoftDeletes)      │
└───────────────┬───────────────────────────────┬───────────────────────┘
                │                                │
        ┌───────▼────────┐              ┌────────▼─────────┐
        │  MariaDB 10.4  │              │  Redis (queue,   │
        │  (InnoDB, FKs) │              │  cache, session) │
        └────────────────┘              └──────────────────┘
                │
        ┌───────▼─────────────────────────────────────────┐
        │ Async workers: Queue Jobs + Horizon              │
        │ (notifications, QR batch generation, exports,    │
        │  fraud scoring, activity-log flush)              │
        └──────────────────────────────────────────────────┘
```

## 2. Layered Design (per module)

Every module follows the same vertical slice:

```
Migration → Model (+ Observer) → Repository(Interface+Eloquent) →
Service → FormRequest → Policy → Controller (Web + Api) →
Routes → Blade views / API Resource → Seeder → Tests
```

- **Controllers** are thin: validate (FormRequest) → authorize (Policy) → delegate (Service) → respond.
- **Services** hold business rules, are transaction-aware, and never touch `$request`.
- **Repositories** are bound in a `RepositoryServiceProvider` (interface → implementation).
- **Observers** stamp `created_by/updated_by/deleted_by` and write the activity log.

## 3. Cross-Cutting Concerns

| Concern            | Mechanism                                                        |
|--------------------|-----------------------------------------------------------------|
| Audit trail        | `Auditable` trait + `ActivityLog` model + global observer       |
| Soft deletes       | `BlameableSoftDeletes` trait (`deleted_by`, `deleted_at`)       |
| UUID public IDs    | `HasUuid` trait — `uuid` column, route-model-bind by uuid       |
| Request context    | `CaptureRequestContext` middleware → ip, ua, browser, device    |
| RBAC               | `spatie/laravel-permission` — roles, permissions, middleware    |
| Authorization      | Policies per model + `permission:` middleware                   |
| Rate limiting      | Named limiters (`api`, `verify`, `otp`) in `AppServiceProvider` |
| API versioning     | `routes/api_v1.php` under `/api/v1` prefix                      |
| Fraud detection    | `FraudService` — duplicate-scan, GPS radius, device lock, lists |

## 4. Directory Structure (key additions)

```
app/
├── Http/
│   ├── Controllers/{Web,Api/V1}/…
│   ├── Requests/{Product,Qr,Verification,…}/…
│   ├── Middleware/ (CaptureRequestContext, EnsureDeviceAllowed, ForceJsonResponse)
│   └── Resources/Api/V1/…
├── Models/ (User, Role via spatie, Company, Brand, Category, Product,
│            Batch, QrCode, Scan, VerificationLog, ActivityLog, …)
├── Observers/ (BlameableObserver, ActivityLogObserver)
├── Policies/
├── Services/{Qr,Verification,Reward,Wallet,Fraud,Export}/…
├── Repositories/{Contracts, Eloquent}/…
├── Support/Traits/ (HasUuid, Auditable, BlameableSoftDeletes)
├── Events/  Listeners/  Jobs/  Notifications/
database/{migrations,seeders,factories}/
resources/views/  (layouts, components, portals/*)
routes/{web.php, api_v1.php, auth.php}
docs/  (this folder)
tests/{Unit,Feature}/
```

## 5. Build Roadmap (module by module)

1. **Foundation** — traits, base migration conventions, audit log, request-context middleware, RepositoryServiceProvider. *(prerequisite)*
2. **Auth + RBAC** — users, roles, permissions, login/OTP/2FA scaffolding, seeded roles.
3. **Product domain** — Company, Brand, Category, Product, Batch, Inventory. ← **current focus**
4. **QR engine** — QrCode generation (single + batch job), printable sheets, secure signed payloads.
5. **Verification engine** — public scan endpoint, Scan + VerificationLog, FraudService (duplicate/GPS/device/blacklist).
6. Wallet → Reward engine → Campaigns → Redeem → Notifications → Reports → Settings → API docs (Swagger) → Mobile.

Each shipped with migrations, models, services, repositories, policies, routes, Blade UI, seeders, and Unit + Feature tests.
