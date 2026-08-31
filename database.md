# CoFund — Database Schema & Relationship Documentation

> Laravel 10 · Crowdfunding Platform

## Overview

**Project:** CoFund
**Framework:** Laravel 10 (PHP 8.1+)
**Database:** MySQL (utf8mb4)
**Total tabel:** 9 (termasuk `users` bawaan Laravel)

Berikut adalah dokumentasi lengkap skema database, kolom, foreign key constraint,
dan relasi Eloquent untuk setiap model.

---

## 1. Migration & Execution Order

Urutan file migration disusun berdasarkan dependency foreign key — tabel yang
di-reference(selalu dibuat lebih dulu):

| Urutan | Timestamp | File | Tabel |
|--------|-----------|------|-------|
| 0 | 2014_10_12_000000 | create_users_table | `users` (bawaan Laravel) |
| 0 | 2014_10_12_100000 | create_password_reset_tokens_table | `password_reset_tokens` (bawaan) |
| 0 | 2019_08_19_000000 | create_failed_jobs_table | `failed_jobs` (bawaan) |
| 0 | 2019_12_14_000001 | create_personal_access_tokens_table | `personal_access_tokens` (bawaan) |
| 1 | 2024_01_01_000001 | create_categories_table | `categories` |
| 2 | 2024_01_01_100000 | add_columns_to_users_table | `users` (modify) |
| 3 | 2024_01_02_000000 | create_campaigns_table | `campaigns` |
| 4 | 2024_01_02_100000 | create_campaign_images_table | `campaign_images` |
| 5 | 2024_01_03_000000 | create_campaign_tiers_table | `campaign_tiers` |
| 6 | 2024_01_03_100000 | create_campaign_updates_table | `campaign_updates` |
| 7 | 2024_01_04_000000 | create_backings_table | `backings` |
| 8 | 2024_01_04_100000 | create_transactions_table | `transactions` |
| 9 | 2024_01_05_000000 | create_notifications_table | `notifications` |

---

## 2. Schema per Tabel

### 2.1 `users` (bawaan Laravel + tambahan)

| Kolom | Type | Default | Keterangan |
|-------|------|---------|------------|
| id | bigint unsigned (auto) | — | Primary key |
| name | varchar(255) | — | Nama lengkap |
| email | varchar(255) unique | — | Email (unik) |
| email_verified_at | timestamp | NULL | Verifikasi email |
| password | varchar(255) | — | Password (hashed) |
| remember_token | varchar(100) | NULL | Token "remember me" |
| role | enum(guest, backer, creator, admin) | `backer` | Peran pengguna |
| balance | decimal(15,2) | `0.00` | Saldo pengguna |
| is_suspended | tinyint(1) | `0` | Status suspensi akun |
| suspended_at | timestamp | NULL | Kapan akun disuspend |
| created_at | timestamp | NULL | — |
| updated_at | timestamp | NULL | — |

**Migration tambahan:** `2024_01_01_100000_add_columns_to_users_table.php`
menambahkan kolom `role` dan `balance`.

### 2.2 `categories`

| Kolom | Type | Default | Keterangan |
|-------|------|---------|------------|
| id | bigint unsigned (auto) | — | Primary key |
| name | varchar(255) | — | Nama kategori |
| slug | varchar(255) unique | — | Slug (unik) |
| created_at | timestamp | NULL | — |
| updated_at | timestamp | NULL | — |

**Relasi:** `hasMany(Campaign::class)` → Campaign memiliki `category_id`

### 2.3 `campaigns`

| Kolom | Type | Default | Keterangan |
|-------|------|---------|------------|
| id | bigint unsigned (auto) | — | Primary key |
| user_id | bigint unsigned | — | FK → users (creator kampanye), onDelete cascade |
| category_id | bigint unsigned | — | FK → categories |
| title | varchar(100) | — | Judul kampanye (maks 100 karakter) |
| slug | varchar(255) unique | — | Slug (unik) |
| description | text | — | Deskripsi kampanye |
| target_amount | decimal(15,2) | — | Target dana |
| collected_amount | decimal(15,2) | `0.00` | Dana terkumpul |
| deadline | date | — | Tanggal akhir kampanye |
| status | enum(draft, review, active, success, failed) | `draft` | Status kampanye |
| video_url | varchar(255) | NULL | URL video promosi |
| rejection_note | text | NULL | Catatan penolakan (oleh admin) |
| reviewed_by | bigint unsigned | NULL | FK → users (admin yang review), nullable |
| reviewed_at | timestamp | NULL | Kapan kampanye direview |
| created_at | timestamp | NULL | — |
| updated_at | timestamp | NULL | — |

### 2.4 `campaign_images`

| Kolom | Type | Default | Keterangan |
|-------|------|---------|------------|
| id | bigint unsigned (auto) | — | Primary key |
| campaign_id | bigint unsigned | — | FK → campaigns, onDelete cascade |
| url | varchar(255) | — | URL gambar |
| is_primary | tinyint(1) | `0` | Apakah gambar utama |
| created_at | timestamp | NULL | — |
| updated_at | timestamp | NULL | — |

### 2.5 `campaign_tiers`

| Kolom | Type | Default | Keterangan |
|-------|------|---------|------------|
| id | bigint unsigned (auto) | — | Primary key |
| campaign_id | bigint unsigned | — | FK → campaigns, onDelete cascade |
| name | varchar(255) | — | Nama tier |
| min_amount | decimal(15,2) | — | Nominal minimum backing |
| quota | int | `0` | Kuota (0 = unlimited) |
| remaining_quota | int | `0` | Kuota tersisa |
| reward_description | text | — | Deskripsi reward |
| created_at | timestamp | NULL | — |
| updated_at | timestamp | NULL | — |

### 2.6 `campaign_updates`

| Kolom | Type | Default | Keterangan |
|-------|------|---------|------------|
| id | bigint unsigned (auto) | — | Primary key |
| campaign_id | bigint unsigned | — | FK → campaigns, onDelete cascade |
| title | varchar(255) | — | Judul update |
| content | text | — | Isi update |
| created_at | timestamp | NULL | — |
| updated_at | timestamp | NULL | — |

### 2.7 `backings`

| Kolom | Type | Default | Keterangan |
|-------|------|---------|------------|
| id | bigint unsigned (auto) | — | Primary key |
| user_id | bigint unsigned | — | FK → users (backer) |
| campaign_id | bigint unsigned | — | FK → campaigns |
| tier_id | bigint unsigned | NULL | FK → campaign_tiers, nullable (backing bebas nominal) |
| amount | decimal(15,2) | — | Jumlah backing |
| status | enum(pending, completed, refunded) | `pending` | Status backing |
| created_at | timestamp | NULL | — |
| updated_at | timestamp | NULL | — |

### 2.8 `transactions`

| Kolom | Type | Default | Keterangan |
|-------|------|---------|------------|
| id | bigint unsigned (auto) | — | Primary key |
| user_id | bigint unsigned | — | FK → users |
| backing_id | bigint unsigned | NULL | FK → backings, nullable (untuk disbursement/platform_fee) |
| campaign_id | bigint unsigned | NULL | FK → campaigns, nullable (untuk disbursement/platform_fee) |
| type | enum(payment, refund, disbursement, platform_fee) | — | Tipe transaksi |
| amount | decimal(15,2) | — | Jumlah transaksi |
| status | enum(pending, success, failed) | `pending` | Status transaksi |
| reference | varchar(255) | NULL | Referensi eksternal |
| created_at | timestamp | NULL | — |
| updated_at | timestamp | NULL | — |

### 2.9 `notifications`

| Kolom | Type | Default | Keterangan |
|-------|------|---------|------------|
| id | bigint unsigned (auto) | — | Primary key |
| user_id | bigint unsigned | — | FK → users, onDelete cascade |
| type | varchar(255) | — | Tipe notifikasi |
| title | varchar(255) | — | Judul notifikasi |
| body | text | — | Isi notifikasi |
| data | json | NULL | Data tambahan |
| read_at | timestamp | NULL | Kapan dibaca |
| created_at | timestamp | NULL | — |
| updated_at | timestamp | NULL | — |

---

## 3. Foreign Key Constraint Summary

### Cascade on Delete (`onDelete('cascade')`)

| Tabel | Kolom | References |
|-------|-------|------------|
| campaigns | user_id | users(id) |
| campaign_images | campaign_id | campaigns(id) |
| campaign_tiers | campaign_id | campaigns(id) |
| campaign_updates | campaign_id | campaigns(id) |
| notifications | user_id | users(id) |

### Restrict (default, no onDelete)

| Tabel | Kolom | References | Nullable |
|-------|-------|------------|----------|
| campaigns | category_id | categories(id) | No |
| campaigns | reviewed_by | users(id) | Yes |
| backings | user_id | users(id) | No |
| backings | campaign_id | campaigns(id) | No |
| backings | tier_id | campaign_tiers(id) | Yes |
| transactions | user_id | users(id) | No |
| transactions | backing_id | backings(id) | Yes |
| transactions | campaign_id | campaigns(id) | Yes |

---

## 4. Eloquent Model Relationship Map

```
┌───────────┐          ┌────────────┐
│   User    │◄─────────┤  Category  │
└─────┬─────┘  1    *  └─────┬──────┘
      │ hasMany            │ hasMany
      │ campaigns()        │ campaigns()
      ▼                    ▼
┌───────────┐          ┌──────────────┐
│ Campaign  │◄─────────┤ CampaignTier │◄──────────┐
└─────┬─────┘  1    *  └──────┬───────┘  hasMany │
      │ hasMany               │ hasMany  │ backings()
      │                       │ backings()     ▼
      │                       ▼        ┌──────────┐
      │                      (tier_id) │ Backing  │◄──┐
      │                                 └────┬─────┘  │ hasMany
      │ hasMany                              │        │ transactions()
      │ images()                        FK   │        ▼
      ▼                         backing_id  │  ┌──────────────┐
┌──────────────┐                          │  │ Transaction  │
│ CampaignImage│                          │  └──────┬───────┘
└──────┬───────┘                          │         │
       │ FK: campaign_id                  │         │ FK: backing_id (nullable)
       ▼                                  │         ▼
┌──────────────┐       ┌───────────────┐  │  ┌──────────────┐
│ Campaign     │← FK   │ CampaignTier  │  │  │ Transaction  │
│ Update       │  camp │               │  │  │              │
└──────────────┘  aign │               │  │  └──────┬───────┘
                      └───────────────┘  │         │
                                        │         │ FK: campaign_id (nullable)
                                        │         ▼
                                        │  ┌──────────────┐
                                        └──│ Transaction  │
                                           │              │
                                           └──────────────┘

┌───────────┐            ┌──────────────┐
│   User    │◄──────┬──FK │  Campaign    │
│ (reviewer)│◄──────┘  │  (reviewed_by)│
│           │ FK     │  └──────────────┘
│           │        │
│           └── FK ──┴──┐
│  reviewed_by         │
└──────────────────────┘

┌───────────┐
│   User    │
└─────┬─────┘
      │ hasMany
      │ notifications()
      ▼
┌──────────────┐
│ Notification │
└──────────────┘
```

### Model → Relationship Methods (Per Model)

#### User
```php
campaigns()        → hasMany(Campaign::class, 'user_id')
backings()         → hasMany(Backing::class, 'user_id')
transactions()     → hasMany(Transaction::class, 'user_id')
notifications()    → hasMany(Notification::class, 'user_id')
```

#### Category
```php
campaigns()        → hasMany(Campaign::class)
```

#### Campaign
```php
creator()          → belongsTo(User::class, 'user_id')
category()         → belongsTo(Category::class)
reviewer()         → belongsTo(User::class, 'reviewed_by')
images()           → hasMany(CampaignImage::class)
tiers()            → hasMany(CampaignTier::class)
updates()          → hasMany(CampaignUpdate::class)
backings()         → hasMany(Backing::class)
```

#### CampaignImage
```php
campaign()         → belongsTo(Campaign::class)
```

#### CampaignTier
```php
campaign()         → belongsTo(Campaign::class)
backings()         → hasMany(Backing::class, 'tier_id')
```

#### CampaignUpdate
```php
campaign()         → belongsTo(Campaign::class)
```

#### Backing
```php
backer()           → belongsTo(User::class, 'user_id')
campaign()         → belongsTo(Campaign::class)
tier()             → belongsTo(CampaignTier::class, 'tier_id')
transactions()     → hasMany(Transaction::class)
```

#### Transaction
```php
user()             → belongsTo(User::class)
backing()          → belongsTo(Backing::class)
campaign()         → belongsTo(Campaign::class)
```

#### Notification
```php
user()             → belongsTo(User::class)
```

---

## 5. Eloquent FK Guessing Rules (Laravel 10)

### hasMany (One-to-Many)
FK di-tebak dari **nama parent model** (snake_case singular + `_id`):
- `Category::campaigns()` → FK = `category_id`
- `Campaign::images()` → FK = `campaign_id`
- `CampaignTier::backings()` → FK default = `campaign_tier_id` → **salah**, perlu eksplisit `= 'tier_id'`

### belongsTo (Many-to-One)
FK di-tebak dari **nama method** (snake_case + `_id`):
- `Campaign::creator()` → FK default = `creator_id` → **salah**, perlu eksplisit `= 'user_id'`
- `Campaign::category()` → FK default = `category_id` → **benar** (cocok)
- `Campaign::reviewer()` → FK default = `reviewer_id` → **salah**, perlu eksplisit `= 'reviewed_by'`
- `Backing::backer()` → FK default = `backer_id` → **salah**, perlu eksplisit `= 'user_id'`
- `Backing::tier()` → FK default = `tier_id` → **benar** (cocok, tapi tetap eksplisit)

---

## 6. File Listing

### Migration Files
```
database/migrations/
├── 2014_10_12_000000_create_users_table.php          (bawaan)
├── 2014_10_12_100000_create_password_reset_tokens_table.php  (bawaan)
├── 2019_08_19_000000_create_failed_jobs_table.php     (bawaan)
├── 2019_12_14_000001_create_personal_access_tokens_table.php  (bawaan)
├── 2024_01_01_000001_create_categories_table.php      (baru)
├── 2024_01_01_100000_add_columns_to_users_table.php   (baru)
├── 2024_01_02_000000_create_campaigns_table.php       (baru)
├── 2024_01_02_100000_create_campaign_images_table.php (baru)
├── 2024_01_03_000000_create_campaign_tiers_table.php  (baru)
├── 2024_01_03_100000_create_campaign_updates_table.php (baru)
├── 2024_01_04_000000_create_backings_table.php        (baru)
├── 2024_01_04_100000_create_transactions_table.php    (baru)
└── 2024_01_05_000000_create_notifications_table.php   (baru)
```

### Model Files
```
app/Models/
├── User.php               (bawaan Laravel, sudah dilengkapi)
├── Category.php           (baru)
├── Campaign.php           (baru)
├── CampaignImage.php      (baru)
├── CampaignTier.php       (baru)
├── CampaignUpdate.php     (baru)
├── Backing.php            (baru)
├── Transaction.php        (baru)
└── Notification.php       (baru)
```

### Resource Files
```
app/Http/Resources/
└── UserResource.php       (baru)
```