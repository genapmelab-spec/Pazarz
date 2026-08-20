# ROLES.md — Role, Permission & Authorization Architecture

---

## 1. Konsep Dasar

Pazarz menggunakan model **Role-Based Access Control (RBAC)** dengan granular permission (bukan sekadar `if role === 'admin'`), agar di masa depan mudah menambah sub-role (mis. `admin_support`, `admin_finance`) tanpa mengubah struktur inti.

- **Role**: kelompok besar (`customer`, `seller`, `admin`).
- **Permission**: hak akses granular (`manage-products`, `view-reports`, dst), dipetakan ke role via `role_permissions`.
- **Resource Ownership**: sebagian aksi tidak cukup dicek lewat permission saja — perlu dicek apakah resource tersebut milik actor (mis. seller hanya boleh edit produk **miliknya sendiri**).
- **Admin Override**: admin dengan permission terkait dapat mengakses/mengubah resource **lintas kepemilikan** (mis. admin dapat menonaktifkan produk seller manapun).

## 2. Authentication vs Authorization

- **Authentication** = memverifikasi identitas ("siapa kamu") — lihat `AUTH.md`.
- **Authorization** = memverifikasi hak akses ("apa yang boleh kamu lakukan") — dijelaskan di dokumen ini, diterapkan di layer API/Controller melalui **Policy** (per-resource ownership check) + **Middleware/Gate** (permission check).

Urutan pengecekan pada setiap request yang butuh otorisasi:
```text
1. Apakah request terautentikasi? (Authentication)
2. Apakah role/permission user mengizinkan aksi ini secara umum? (Permission)
3. Apakah user adalah owner dari resource spesifik ini, ATAU punya admin override? (Ownership)
```

## 3. Resource Ownership Rules

| Resource | Owner | Catatan |
|---|---|---|
| Product | Seller (via Store) | Seller hanya bisa CRUD produk milik store-nya sendiri |
| Order (customer view) | Customer (pemilik order) | Customer hanya bisa lihat order miliknya |
| Sub-Order (seller view) | Seller (via Store) | Seller hanya bisa proses sub-order yang store-nya terlibat |
| Store Settings | Seller pemilik store | 1 seller = 1 store |
| Review | Customer penulis (untuk edit/hapus) | Seller hanya boleh membalas (`seller_reply`), tidak edit isi review |
| Coupon (store-level) | Seller pemilik store | Coupon platform-wide hanya dibuat Admin |

Admin dengan permission relevan (`manage-products`, `manage-orders`, dst) memiliki **override** terhadap seluruh rule di atas untuk keperluan moderasi/support.

## 4. Full Permission Matrix

Skala: **✓** = diizinkan, **Own** = hanya milik sendiri, **–** = tidak diizinkan.

| Capability | Customer | Seller | Admin |
|---|---:|---:|---:|
| Register & Login | ✓ | ✓ | – (dibuat oleh Super Admin) |
| Browse & search produk | ✓ | ✓ | ✓ |
| Kelola wishlist & cart | ✓ | – | – |
| Checkout & bayar | ✓ | – | – |
| Lihat order milik sendiri | Own | – | ✓ (semua) |
| Beri review produk yang dibeli | Own | – | – (moderasi saja) |
| Daftar sebagai seller | ✓ | – | – |
| Kelola profil toko | – | Own | ✓ (override) |
| Kelola produk & varian | – | Own | ✓ (override/moderasi) |
| Kelola stok/inventory | – | Own | – (lihat saja) |
| Proses sub-order (confirm/ship) | – | Own | – (lihat saja) |
| Balas review | – | Own | – |
| Buat promosi/kupon toko | – | Own | – |
| Lihat analitik toko sendiri | – | Own | ✓ (semua toko) |
| Kelola kategori & atribut global | – | – | ✓ |
| Verifikasi pendaftaran seller | – | – | ✓ |
| Suspend/aktifkan user atau seller | – | – | ✓ |
| Moderasi produk/review/report | – | – | ✓ |
| Kelola & mediasi dispute | Own (ajukan) | Own (respon) | ✓ (mediasi & putuskan) |
| Buat kupon platform-wide | – | – | ✓ |
| Lihat laporan & analitik platform | – | – | ✓ |
| Kelola pengaturan platform | – | – | ✓ |
| Lihat audit log | – | – | ✓ |

## 5. Permission List (Granular — untuk `permissions` table)

```text
Group: user
- view-users, manage-users, suspend-users

Group: seller
- view-sellers, verify-sellers, manage-sellers

Group: store
- manage-own-store, view-all-stores

Group: product
- manage-own-products, view-all-products, moderate-products

Group: category
- manage-categories

Group: order
- view-own-orders (customer), view-own-sub-orders (seller),
  process-own-sub-orders, view-all-orders (admin)

Group: payment
- view-own-payments, view-all-payments (admin)

Group: review
- create-review (customer), reply-review (seller), moderate-reviews (admin)

Group: promotion
- manage-own-promotions (seller), manage-platform-promotions (admin)

Group: dispute
- raise-dispute, respond-dispute, mediate-disputes

Group: report
- submit-report, review-reports

Group: analytics
- view-own-store-analytics, view-platform-analytics

Group: platform
- manage-platform-settings, view-audit-logs
```

## 6. Catatan Implementasi (untuk fase development nanti)

- Setiap role default akan memiliki permission set tetap (seed data), namun struktur `role_permissions` memungkinkan kustomisasi tanpa migrasi ulang.
- Pengecekan **ownership** sebaiknya diimplementasikan sebagai Laravel Policy per model (`ProductPolicy`, `SubOrderPolicy`, dst), dipanggil di Controller/Form Request — bukan hardcode di dalam Controller.
- Endpoint API (`API.md`) harus mendokumentasikan permission yang dibutuhkan per endpoint secara eksplisit.
