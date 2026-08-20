# DATABASE.md — Database Design Standard

Dokumen ini melengkapi `ERD.md` dengan konvensi teknis yang akan langsung dipakai saat menulis Laravel migration.

---

## 1. Naming Convention

- **Table name:** plural, `snake_case` → `products`, `sub_orders`, `product_attribute_values`.
- **Pivot table:** gabungan nama tabel singular urut alfabetis atau nama deskriptif eksplisit jika pivot punya kolom tambahan → `promotion_products`, `role_permissions`.
- **Column name:** `snake_case`, deskriptif, hindari singkatan ambigu → `verification_status` bukan `verif_stat`.
- **Foreign key:** `{singular_table}_id` → `product_id`, `store_id`, `sub_order_id`.
- **Boolean column:** prefix `is_`/`has_` → `is_default`, `is_primary`, `has_variants`.
- **Status column:** selalu `status` (string/enum), nilai dijelaskan di dokumentasi tabel masing-masing (`ERD.md`), bukan angka magic number.

## 2. Primary Key Strategy

- Seluruh tabel menggunakan **auto-increment BIGINT UNSIGNED** sebagai primary key internal (`id`) untuk performa index & simplicity join.
- Untuk entity yang **ter-ekspos di URL publik** (mis. `orders.order_number`, `stores.slug`), digunakan kolom **tambahan** yang unik dan non-sequential (UUID/random string/slug) — bukan mengganti PK, agar internal ID tidak bocor ke publik namun query internal tetap efisien.

## 3. Foreign Key Strategy

- Seluruh FK didefinisikan dengan constraint eksplisit (`foreignId()->constrained()`).
- **On delete behavior** ditentukan per relasi:
  - Relasi yang **wajib ikut hilang** jika parent hilang (mis. `product_images` saat `products` dihapus permanen) → `cascade`.
  - Relasi yang **harus dipertahankan untuk histori** (mis. `order_items` terhadap `product_variants`) → **tidak cascade delete**; produk memakai **soft delete**, bukan hard delete, agar FK tetap valid.
  - Relasi opsional (mis. `promotion.store_id` saat kupon platform-wide) → `nullOnDelete()` jika kolom nullable.

## 4. Index Strategy

- Setiap FK otomatis diberi index (default Laravel).
- Kolom yang sering dipakai untuk filter/search diberi index tambahan: `products.status`, `orders.status`, `sub_orders.status`, `users.email` (unique), `stores.slug` (unique), `products.slug` (unique per store).
- Kolom pencarian teks (`products.name`) mempertimbangkan **full-text index** untuk mendukung fitur search.
- Composite index untuk query gabungan yang sering terjadi, mis. (`store_id`,`status`) pada `products` untuk query "produk aktif milik toko X".

## 5. Soft Delete Strategy

Soft delete (`deleted_at`) diterapkan pada entity yang **berdampak ke histori transaksi** jika dihapus keras:
```text
users, sellers, stores, products, categories
```
Entity murni operasional/log (mis. `notifications`, `audit_logs`, `shipment_tracking_events`) **tidak** memakai soft delete — dihapus keras jika memang perlu, atau di-retain permanen untuk audit log.

## 6. Timestamp Strategy

- Seluruh tabel memiliki `created_at` dan `updated_at` standar Laravel.
- Tabel dengan event waktu spesifik menambahkan kolom timestamp bernama jelas, bukan reuse `updated_at`: `email_verified_at`, `paid_at`, `shipped_at`, `delivered_at`, `resolved_at`, `verified_at`.

## 7. Status Fields

- Semua kolom status memakai tipe **string pendek dengan enum-like values** (didefinisikan di level aplikasi/constant, bukan native DB ENUM) agar mudah ditambah nilai baru tanpa migration ubah kolom.
- Setiap perubahan status pada entity kritikal (`orders`, `sub_orders`, `payments`, `disputes`) sebaiknya juga tercermin di `audit_logs` (untuk aksi admin) atau tabel event khusus (`shipment_tracking_events` untuk shipment) — bukan hanya overwrite kolom status tanpa jejak.

## 8. Data Normalization

- Skema dinormalisasi hingga **3NF** untuk data master (users, products, categories, dst).
- **Denormalisasi disengaja** dilakukan pada data transaksional historis (`order_items.price_snapshot`, `order_items.product_name_snapshot`) — ini bukan pelanggaran normalisasi, melainkan snapshot yang memang secara bisnis harus immutable terhadap perubahan master data di kemudian hari.

## 9. Data Integrity

- Constraint uniqueness diterapkan di level database (bukan hanya validasi aplikasi): `users.email`, `stores.slug`, `product_variants.sku`, `coupons.code`.
- Constraint composite unique untuk mencegah duplikasi relasi: `(cart_id, product_variant_id)`, `(wishlist_id, product_id)`, `(role_id, permission_id)`, `(user_id, store_id)` pada `seller_followers`.
- Validasi nilai (mis. `reviews.rating` antara 1–5) diterapkan di level aplikasi (Form Request) — didokumentasikan bersama endpoint terkait di `API.md`.

## 10. Audit Strategy

- `audit_logs` mencatat seluruh **aksi admin sensitif**: perubahan status user/seller, penghapusan produk oleh admin, keputusan dispute, perubahan pengaturan platform.
- Format `changes` disimpan sebagai JSON berisi before/after value untuk field yang berubah, memudahkan investigasi.
- `shipment_tracking_events` berfungsi sebagai audit trail khusus untuk perjalanan pengiriman, dipisah dari `audit_logs` karena sifatnya berbeda (event eksternal dari courier, bukan aksi user internal).

## 11. Migration Ordering (Panduan untuk Fase Development)

Urutan migration harus mengikuti dependency FK, garis besar:
```text
1. roles, permissions, role_permissions
2. users
3. sellers, stores
4. addresses
5. categories, product_attributes
6. products, product_images, product_variants, product_attribute_values
7. inventories
8. carts, cart_items, wishlists, wishlist_items
9. orders, sub_orders, order_items
10. payments, shipments, shipment_tracking_events
11. reviews, review_images
12. promotions, promotion_products, coupons, coupon_usages
13. notifications, seller_followers
14. reports, disputes, dispute_messages
15. audit_logs
```
