# Database Design — Foundation + Product + QR + Verification

MariaDB 10.4 · InnoDB · utf8mb4 · foreign keys + indexes + soft deletes everywhere.

## Common columns (every business table)

| Column       | Type              | Notes                                  |
|--------------|-------------------|----------------------------------------|
| id           | BIGINT UNSIGNED PK| auto-increment, internal               |
| uuid         | CHAR(36) UNIQUE   | public identifier, route binding       |
| …domain cols…|                   |                                        |
| created_by   | BIGINT FK users   | nullable, set by observer              |
| updated_by   | BIGINT FK users   | nullable                               |
| deleted_by   | BIGINT FK users   | nullable                               |
| created_at   | TIMESTAMP         |                                        |
| updated_at   | TIMESTAMP         |                                        |
| deleted_at   | TIMESTAMP NULL    | soft delete                            |

Request-context (ip, user_agent, browser, device) is captured centrally in the
`activity_logs` table (one row per meaningful action) rather than bloating every
table — keeps business tables clean while remaining fully audit-ready.

## ER Diagram (core)

```
companies ──1:N── brands ──1:N── products ──1:N── batches ──1:N── qr_codes
    │                               │                                 │
    │                          categories (N:1 products)              │
    │                                                                 1:N
    └───1:N── users (staff)                                         scans
                                                                      │
                                                              verification_logs
```

## Tables

### companies
- name, legal_name, slug(uniq), email, phone, gstin, logo_path, address, city,
  state, country, pincode, status(enum: active/inactive), settings(json)
- + common columns. Index: slug, status.

### brands
- company_id FK, name, slug(uniq per company), logo_path, description, status
- + common. Unique(company_id, slug). Index: company_id, status.

### categories
- company_id FK, parent_id FK self (nullable, nested), name, slug, description, status
- + common. Unique(company_id, slug). Index: company_id, parent_id.

### products
- company_id FK, brand_id FK, category_id FK, name, slug, sku(uniq per company),
  hsn_code, description, unit(enum), mrp DECIMAL(12,2), reward_points INT,
  image_path, status(enum), meta(json)
- + common. Unique(company_id, sku). Index: brand_id, category_id, status.

### batches
- product_id FK, code(uniq per product), manufacture_date, expiry_date,
  quantity INT, qr_generated INT default 0, status(enum: draft/generating/active/closed)
- + common. Unique(product_id, code). Index: product_id, status.

### qr_codes  (the physical/printable unit)
- batch_id FK, product_id FK (denormalized for fast verify),
  code(uniq, CHAR(32) — random secure token),
  serial(uniq per batch), payload_hash(HMAC signature),
  image_path, short_url, reward_points INT,
  status(enum: generated/printed/active/scanned/verified/blocked),
  first_scanned_at, scan_count INT default 0, activated_at
- + common. Unique(code). Index: batch_id, product_id, status. Heavy read path.

### scans  (every scan attempt, valid or not — fraud raw feed)
- qr_code_id FK (nullable if code unknown), raw_code, user_id FK (nullable),
  result(enum: valid/duplicate/invalid/blocked/expired),
  latitude, longitude, accuracy, ip_address, user_agent, browser, device,
  is_fraud_suspected BOOL, fraud_reasons(json)
- + common. Index: qr_code_id, user_id, result, created_at.

### verification_logs  (successful, reward-eligible verifications)
- qr_code_id FK, scan_id FK, user_id FK, product_id FK, batch_id FK,
  reward_points INT, latitude, longitude, verified_at, status(enum)
- + common. Unique(qr_code_id, user_id) where first-verify wins. Index: user_id, product_id.

### activity_logs  (universal audit + request context)
- log_name, description, subject_type, subject_id, causer_id FK users,
  event(enum: created/updated/deleted/restored/login/scan/verify/…),
  properties(json: old/new), ip_address, user_agent, browser, device, url, method
- created_at. Index: subject_type+subject_id, causer_id, event, created_at.

### blacklists / whitelists
- type(enum: device/ip/user/code), value(uniq per type), reason, expires_at, status
- + common. Unique(type, value).

## Fraud rules enforced at verify time
1. **Duplicate scan** — code already `verified` → result=duplicate, no reward.
2. **GPS radius** — if company enforces geo, scan outside allowed radius → suspect.
3. **Device lock** — same device_id scanning > N distinct codes/hour → suspect.
4. **Blacklist** — ip/device/user/code on blacklist → result=blocked.
5. **Expiry** — batch.expiry_date passed → result=expired.
