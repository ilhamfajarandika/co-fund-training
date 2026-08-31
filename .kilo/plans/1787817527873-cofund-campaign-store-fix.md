# CoFund — Audit Modul Campaign (4.1–4.4)

## Ringkasan
Implementasi Modul Campaign pada project CoFund memiliki beberapa celah kritis terhadap spesifikasi CoFund Feature v1.0.0. Berikut adalah audit menyeluruh beserta rencana perbaikan.

---

## Tahap 1 — Mapping Requirement ke Implementasi

| Requirement | File Implementasi | Status |
|-------------|-------------------|--------|
| Title max 100 char | StoreCampaignRequest | ✅ |
| Slug otomatis | CampaignService::create() | ✅ |
| Slug bisa diedit manual | — | ❌ |
| Category wajib | StoreCampaignRequest | ✅ |
| Target min Rp100.000 | StoreCampaignRequest | ✅ |
| Deadline min H+7 | StoreCampaignRequest | ✅ |
| Video URL nullable | StoreCampaignRequest + Migration | ✅ |
| Upload 1–5 gambar | StoreCampaignRequest + CampaignService | ✅ |
| Simpan gambar ke campaign_images | CampaignService | ✅ |
| Status awal review | CampaignService | ✅ |
| Transaction benar | CampaignService | ✅ |
| Edit hanya saat draft | — | ❌ |
| Active ditolak untuk edit | — | ❌ |
| List campaign (kategori, status, terbaru, terpopuler) | CampaignService::getAll() | ❌ |
| Detail campaign lengkap | CampaignController::show() | ❌ |
| Campaign Update endpoint | — | ❌ |
| Creator only untuk update | — | ❌ |
| Hanya saat active untuk update | — | ❌ |
| Notifikasi backer untuk update | — | ❌ |

---

## Tahap 2 — Checklist Detail

### 4.1 Buat Kampanye (Creator)
- [x] Validasi title max 100
- [x] Slug otomatis
- [ ] Slug bisa diedit manual
- [x] Category wajib
- [x] Target minimal Rp100.000
- [x] Deadline minimal H+7
- [x] Video nullable
- [x] Upload 1–5 gambar
- [x] Simpan gambar ke campaign_images
- [x] Status awal review
- [x] Transaction benar

### 4.2 Edit Campaign
- [ ] Hanya draft yang boleh diedit
- [ ] Active ditolak
- [x] Tidak mengubah status sembarangan (tidak ada status di UpdateCampaignRequest)

### 4.3 Lihat Campaign
- [x] Endpoint list ada (`GET /api/v1/campaigns`)
- [ ] Filter kategori
- [ ] Filter status
- [x] Sort terbaru (`latest()`)
- [ ] Sort populer
- [ ] Endpoint detail lengkap (`GET /api/v1/campaigns/{id}`)

### 4.4 Campaign Update
- [ ] Endpoint ada
- [ ] Creator only
- [ ] Hanya saat active
- [ ] Notifikasi backer

---

## Tahap 3 — Gap Analysis

### Gap 1: Slug tidak bisa diedit manual
- **Requirement**: Slug otomatis dari judul, dapat diedit manual
- **File**: CampaignService::create(), UpdateCampaignRequest
- **Penyebab**: Service selalu generate slug dari title. Tidak ada field `slug` di request dan tidak ada update slug di service.
- **Dampak**: Creator tidak bisa menyesuaikan slug untuk SEO.
- **Prioritas**: Low

### Gap 2: Edit campaign tanpa pengecekan status draft
- **Requirement**: Hanya boleh edit saat status draft. Setelah active hanya boleh membuat Campaign Update.
- **File**: CampaignController::update(), CampaignService::update(), UpdateCampaignRequest
- **Penyebab**: Tidak ada validasi `$campaign->status === 'draft'` di controller/service.
- **Dampak**: Creator bisa mengedit campaign yang sudah active/review, melanggar business rule.
- **Prioritas**: Critical

### Gap 3: List campaign tanpa filter dan sort populer
- **Requirement**: List mendukung filter kategori, filter status, sort terbaru, sort populer.
- **File**: CampaignService::getAll(), CampaignController::index()
- **Penyebab**: Method `getAll()` hanya `latest()->paginate(10)`. Tidak membaca query parameter untuk filter/sort.
- **Dampak**: User tidak bisa mencari/menyortir campaign sesuai preferensi.
- **Prioritas**: High

### Gap 4: Detail campaign tidak lengkap dan return salah data
- **Requirement**: Detail menampilkan info campaign, progress funding, persentase, sisa hari, daftar tier, daftar backer, campaign update.
- **File**: CampaignController::show()
- **Penyebab**: 
  - Method `show()` mengembalikan `$this->campaignService->getAll()` (list) bukan campaign tunggal.
  - Tidak ada eager loading untuk `images`, `backings`, `tiers`, `updates`.
  - Tidak ada perhitungan progress funding dan persentase.
- **Dampak**: Detail campaign tidak berfungsi dan tidak menampilkan data lengkap.
- **Prioritas**: Critical

### Gap 5: Campaign Update tidak ada
- **Requirement**: Creator dapat membuat update saat campaign active. Semua backer menerima notifikasi.
- **File**: — (tidak ada model, controller, service, route)
- **Penyebab**: Migration `campaign_updates` ada, tapi tidak ada model `CampaignUpdate`, controller, service, atau route.
- **Dampak**: Fitur Campaign Update tidak ada sama sekali.
- **Prioritas**: High

### Gap 6: Kolom current_amount tidak ada di database
- **Requirement**: Progress funding dan perhitungan current_amount
- **File**: BackingService::store(), Campaign model
- **Penyebab**: Migration `campaigns` tidak memiliki kolom `current_amount`, tapi `BackingService` menggunakan `$campaign->increment('current_amount', ...)` dan `$campaign->current_amount`.
- **Dampak**: BackingService akan error (SQL error) saat donasi diproses.
- **Prioritas**: Critical

### Gap 7: Status enum mismatch di BackingService
- **Requirement**: Status campaign dan backing sesuai enum migration
- **File**: BackingService::store()
- **Penyebab**: 
  - BackingService menyimpan backing dengan `status = 'held'`, tapi migration `backings` hanya allow `['pending', 'completed', 'refunded']`.
  - BackingService mengupdate campaign ke `status = 'funded'`, tapi migration `campaigns` hanya allow `['draft', 'review', 'active', 'success', 'failed']`.
- **Dampak**: SQL error saat donasi dan saat campaign mencapai target.
- **Prioritas**: Critical

### Gap 8: show() typo dan response salah
- **Requirement**: Detail campaign
- **File**: CampaignController::show()
- **Penyebab**: 
  - Typo: "Campaign not fonud"
  - Return `$this->campaignService->getAll()` bukan campaign yang diminta.
- **Dampak**: Detail campaign tidak berfungsi.
- **Prioritas**: Critical

### Gap 9: Tidak ada Policy/Middleware untuk authorization
- **Requirement**: Creator only untuk edit/update campaign
- **File**: — (tidak ada Policy)
- **Penyebab**: Controller menggunakan `auth:sanctum` tapi tidak ada pengecekan ownership atau role di level policy.
- **Dampak**: authorization bergantung pada logic di service/controller, bukan terpusat di Policy.
- **Prioritas**: Medium

### Gap 10: Tidak ada Resource untuk response formatting
- **Requirement**: Standar coding Laravel
- **File**: — (tidak ada CampaignResource, CampaignImageResource)
- **Penyebab**: Controller langsung return model sebagai JSON.
- **Dampak**: Response API bisa menampilkan field yang tidak diinginkan (misal `updated_at`, `deleted_at`). Tidak ada transformasi data.
- **Prioritas**: Low

---

## Tahap 4 — Rencana Perbaikan

### Prioritas 1 (Critical) — Perbaiki Bug yang Menghambat Fitur

1. **Tambah kolom `current_amount` ke migration campaigns**
   - Buat migration baru: `$table->decimal('current_amount', 15, 2)->default(0);`
   - File: `database/migrations/xxxx_xx_xx_xxxxxx_add_current_amount_to_campaigns_table.php`

2. **Perbaiki BackingService::store()**
   - Ganti `status = 'held'` menjadi `status = 'pending'`
   - Ganti `status = 'funded'` menjadi `status = 'success'` (atau hapus logic ini jika tidak sesuai spec)
   - File: `app/Services/BackingService.php`

3. **Perbaiki CampaignController::show()**
   - Return single campaign dengan `images`, `backings`, `tiers`, `updates` di-eager loaded.
   - Perbaiki typo "fonud" → "found"
   - File: `app/Http/Controllers/Api/CampaignController.php`

4. **Tambah pengecekan status draft di UpdateCampaign**
   - Di `CampaignService::update()`, tambahkan validasi `$campaign->status === 'draft'`
   - File: `app/Services/CampaignService.php`

### Prioritas 2 (High) — Implementasi Fitur yang Belum Ada

5. **Implementasi filter dan sort list campaign**
   - Tambahkan query parameter: `?category_id=1&status=active&sort=latest|popular`
   - Sort populer: berdasarkan jumlah backing atau total amount.
   - File: `app/Services/CampaignService.php`, `app/Http/Controllers/Api/CampaignController.php`

6. **Implementasi Campaign Update**
   - Buat model `CampaignUpdate`
   - Buat request `StoreCampaignUpdateRequest`
   - Buat service method `createUpdate()` di CampaignService
   - Buat controller method di CampaignController (atau controller terpisah)
   - Tambah route
   - Validasi: campaign harus active, user adalah creator
   - Kirim notifikasi ke semua backer
   - File baru: `app/Models/CampaignUpdate.php`, `app/Http/Requests/Campaign/StoreCampaignUpdateRequest.php`
   - File edit: `app/Services/CampaignService.php`, `app/Http/Controllers/Api/CampaignController.php`, `routes/api.php`

7. **Edit slug bisa diedit manual**
   - Tambah field `slug` ke `UpdateCampaignRequest` (hanya jika draft)
   - Update logic di service untuk handle slug update
   - File: `app/Http/Requests/Campaign/UpdateCampaignRequest.php`, `app/Services/CampaignService.php`

### Prioritas 3 (Medium) — Peningkatan Arsitektur

8. **Buat Policy untuk Campaign**
   - `viewAny`, `view`, `create`, `update`, `delete`
   - File baru: `app/Policies/CampaignPolicy.php`
   - Register di `AuthServiceProvider`

9. **Buat Resource classes**
   - `CampaignResource`, `CampaignImageResource`, `CampaignUpdateResource`
   - File baru di `app/Http/Resources/`

### Prioritas 4 (Low) — Bug Minor

10. **Perbaiki typo di show()** (sudah termasuk di Prioritas 1)

---

## Urutan Implementasi yang Disarankan

1. Migration `current_amount`
2. Perbaiki BackingService (status enum)
3. Perbaiki CampaignController::show()
4. Tambah validasi draft di update
5. Filter/sort list campaign
6. Campaign Update (model + request + service + controller + route + notifikasi)
7. Slug editable
8. Policy (opsional untuk sprint ini)
9. Resource classes (opsional untuk sprint ini)

---

## Catatan

- Migration initial `create_campaigns_table.php` seharusnya sudah diperbaiki di plan sebelumnya.
- Model `CampaignUpdate` dan `CampaignTier` belum dibuat meskipun migration-nya sudah ada.
- Fitur admin approval (draft → review → active / draft kembali) belum diimplementasi sama sekali.
- Progress funding (persentase, sisa hari) belum ada di response detail.
