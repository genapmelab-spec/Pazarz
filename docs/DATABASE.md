# DATABASE.md — Database Design

Dokumen ini adalah acuan tunggal untuk skema database Pazarz: entity, relasi, dan konvensi teknis yang akan langsung dipakai saat menulis Laravel migration pada fase development. Historical order harus tetap aman terhadap perubahan master data — lihat §5 (Snapshot Fields).

---

## 1. Entity List (Final)

```text
users
roles
permissions
role_permissions (pivot)
sellers
stores
addresses
categories
products
product_images
product_variants
product_attributes
product_attribute_values
inventories
carts
cart_items
wishlists
wishlist_items
orders
sub_orders
order_items
payments
shipments
shipment_tracking_events
reviews
review_images
promotions
promotion_products
coupons
coupon_usages
notifications
seller_followers
reports
disputes
dispute_messages
audit_logs
```

> `role_permissions`, `shipment_tracking_events`, `coupon_usages`, dan `dispute_messages` ditambahkan di luar daftar minimal awal (`users, sellers, addresses, categories, products, product_images, product_variants, carts, cart_items, orders, sub_orders, order_items`) karena diperlukan secara business logic — lihat penjelasan masing-masing di §2.

---

## 2. Entity Detail

Untuk setiap tabel: Purpose · Columns kunci · Primary/Foreign Key · Relationships · Constraints · Indexes.

### 2.1 `users`
- **Purpose:** Menyimpan seluruh akun (customer, seller, admin) dalam satu tabel — role membedakan kapabilitas.
- **Columns:** `id` (PK), `name`, `email` (unique), `password`, `phone`, `role_id` (FK), `email_verified_at`, `status` (active/suspended), `avatar_url`, `deleted_at`, timestamps.
- **Relationships:** 1—1 dengan `sellers` (opsional, jika user adalah seller); 1—N dengan `addresses`, `orders`, `carts`, `wishlists`, `reviews`, `notifications`.
- **Constraints:** `email` unique; `role_id` not null.
- **Indexes:** `email`, `role_id`, `status`.
- **Soft delete:** ya.

### 2.2 `roles`
- **Purpose:** Master role (customer, seller, admin, dan sub-role admin jika diperlukan mis. `admin_support`).
- **Columns:** `id`, `name` (unique), `slug`, `description`.
- **Relationships:** 1—N ke `users`; M—N ke `permissions` via `role_permissions`.

### 2.3 `permissions`
- **Purpose:** Daftar granular permission (`manage-products`, `manage-users`, dst.) — lihat `ARCHITECTURE.md` §7.
- **Columns:** `id`, `name` (unique), `slug`, `group` (mis. "product", "order", "user").
- **Relationships:** M—N ke `roles`.

### 2.4 `role_permissions`
- **Purpose:** Pivot table role↔permission.
- **Columns:** `role_id` (FK), `permission_id` (FK).
- **Constraints:** Composite unique (`role_id`,`permission_id`).

### 2.5 `sellers`
- **Purpose:** Data profil penjual (extension dari `users` ketika role = seller), termasuk status verifikasi.
- **Columns:** `id`, `user_id` (FK, unique), `business_name`, `business_type`, `tax_id`, `verification_status` (pending/verified/rejected), `verified_at`, `commission_rate`.
- **Relationships:** 1—1 dengan `users`; 1—1 dengan `stores`.
- **Indexes:** `user_id` (unique), `verification_status`.
- **Soft delete:** ya.

### 2.6 `stores`
- **Purpose:** Etalase publik seller (nama toko, deskripsi, banner, rating agregat).
- **Columns:** `id`, `seller_id` (FK, unique), `name`, `slug` (unique), `logo_url`, `banner_url`, `description`, `rating_avg`, `rating_count`, `status` (active/inactive), `address_id` (FK).
- **Relationships:** 1—N ke `products`; 1—N ke `seller_followers`.
- **Indexes:** `slug` (unique), `status`.

### 2.7 `addresses`
- **Purpose:** Alamat milik user (pengiriman) maupun store (asal pengiriman) — polymorphic.
- **Columns:** `id`, `addressable_id`, `addressable_type` (polymorphic: `User`/`Store`), `label`, `recipient_name`, `phone`, `province`, `city`, `district`, `postal_code`, `full_address`, `latitude`, `longitude`, `is_default`.
- **Relationships:** Polymorphic ke `users` dan `stores`.
- **Indexes:** (`addressable_id`,`addressable_type`).

### 2.8 `categories`
- **Purpose:** Kategori produk hierarkis (self-referencing untuk sub-kategori).
- **Columns:** `id`, `parent_id` (FK nullable, self-reference), `name`, `slug` (unique), `icon_url`, `is_active`, `sort_order`.
- **Relationships:** 1—N ke `products`; self 1—N (parent-child).
- **Indexes:** `slug`, `parent_id`.
- **Soft delete:** ya.

### 2.9 `products`
- **Purpose:** Produk inti milik sebuah store.
- **Columns:** `id`, `store_id` (FK), `category_id` (FK), `name`, `slug`, `description`, `base_price`, `status` (draft/active/inactive/archived), `weight_grams`, `rating_avg`, `rating_count`, `sold_count`, `deleted_at`.
- **Relationships:** 1—N ke `product_variants`, `product_images`, `reviews`, `order_items` (via variant); M—N ke `promotions` via `promotion_products`.
- **Constraints:** `slug` unique per store.
- **Indexes:** `store_id`, `category_id`, `status`, full-text pada `name`.
- **Soft delete:** ya.

### 2.10 `product_images`
- **Purpose:** Galeri gambar produk (banyak per produk).
- **Columns:** `id`, `product_id` (FK), `url`, `sort_order`, `is_primary`.
- **Relationships:** N—1 ke `products`.

### 2.11 `product_variants`
- **Purpose:** Kombinasi atribut (mis. Merah/L) sebagai unit jual dengan SKU & harga sendiri.
- **Columns:** `id`, `product_id` (FK), `sku` (unique), `price` (override, nullable = pakai `base_price`), `image_id` (FK nullable ke `product_images`).
- **Relationships:** 1—1 ke `inventories`; N—1 ke `products`; N—N ke `product_attribute_values` via §2.13.
- **Indexes:** `sku` (unique), `product_id`.

### 2.12 `product_attributes`
- **Purpose:** Definisi jenis atribut (mis. "Warna", "Ukuran") — global/reusable per kategori.
- **Columns:** `id`, `name`, `category_id` (FK nullable jika global).

### 2.13 `product_attribute_values`
- **Purpose:** Nilai konkret suatu atribut (mis. "Merah") sekaligus menjadi pivot ke variant.
- **Columns:** `id`, `product_attribute_id` (FK), `product_variant_id` (FK), `value`.
- **Constraints:** Composite unique (`product_variant_id`,`product_attribute_id`).

### 2.14 `inventories`
- **Purpose:** Stok per variant, terpisah dari data produk agar update stok tidak mengunci tabel produk.
- **Columns:** `id`, `product_variant_id` (FK, unique), `quantity`, `reserved_quantity`, `low_stock_threshold`.
- **Relationships:** 1—1 ke `product_variants`.
- **Indexes:** `product_variant_id` (unique).

### 2.15 `carts` & 2.16 `cart_items`
- **Purpose:** Keranjang aktif user (1 cart per user) dan isinya per variant.
- **Columns (`carts`):** `id`, `user_id` (FK, unique).
- **Columns (`cart_items`):** `id`, `cart_id` (FK), `product_variant_id` (FK), `quantity`, `price_snapshot`.
- **Constraints:** Composite unique (`cart_id`,`product_variant_id`).

### 2.17 `wishlists` & 2.18 `wishlist_items`
- **Purpose:** Daftar simpan produk favorit user.
- **Columns (`wishlists`):** `id`, `user_id` (FK, unique).
- **Columns (`wishlist_items`):** `id`, `wishlist_id` (FK), `product_id` (FK).
- **Constraints:** Composite unique (`wishlist_id`,`product_id`).

### 2.19 `orders`
- **Purpose:** Order induk milik satu transaksi checkout customer (bisa mencakup banyak seller).
- **Columns:** `id`, `order_number` (unique), `user_id` (FK), `shipping_address_id` (FK), `subtotal`, `shipping_total`, `discount_total`, `grand_total`, `status` (pending_payment/paid/processing/completed/cancelled), `placed_at`.
- **Relationships:** 1—N ke `sub_orders`; 1—1 ke `payments` (umumnya 1 payment per order).
- **Indexes:** `order_number` (unique), `user_id`, `status`.

### 2.20 `sub_orders`
- **Purpose:** Pecahan order per seller — unit kerja utama bagi seller (proses, kirim, selesai per sub-order).
- **Columns:** `id`, `order_id` (FK), `store_id` (FK), `subtotal`, `shipping_cost`, `status` (pending/confirmed/processing/shipped/completed/cancelled), `cancelled_reason`.
- **Relationships:** 1—N ke `order_items`; 1—1 ke `shipments`.
- **Indexes:** `order_id`, `store_id`, `status`.

### 2.21 `order_items`
- **Purpose:** Baris item dalam sebuah sub-order, **snapshot** harga saat transaksi agar riwayat aman terhadap perubahan master data.
- **Columns:** `id`, `sub_order_id` (FK), `product_variant_id` (FK), `product_name_snapshot`, `variant_label_snapshot`, `price_snapshot` (berisi SKU pada snapshot), `quantity`, `subtotal`.
- **Relationships:** N—1 ke `sub_orders`; N—1 ke `product_variants`.

### 2.22 `payments`
- **Purpose:** Catatan transaksi pembayaran terhadap `orders` (via payment gateway eksternal).
- **Columns:** `id`, `order_id` (FK, unique), `method` (va/e-wallet/card), `provider`, `provider_reference`, `amount`, `status` (pending/success/failed/refunded), `paid_at`.
- **Indexes:** `order_id` (unique), `status`, `provider_reference`.

### 2.23 `shipments`
- **Purpose:** Data pengiriman per sub-order.
- **Columns:** `id`, `sub_order_id` (FK, unique), `courier`, `tracking_number`, `status` (pending/picked_up/in_transit/delivered/failed), `shipped_at`, `delivered_at`.
- **Relationships:** 1—N ke `shipment_tracking_events`.

### 2.24 `shipment_tracking_events`
- **Purpose:** Riwayat status pengiriman granular (untuk timeline tracking di UI).
- **Columns:** `id`, `shipment_id` (FK), `status`, `description`, `location`, `occurred_at`.

### 2.25 `reviews`
- **Purpose:** Ulasan customer terhadap produk yang telah dibeli (terikat ke `order_item` untuk validasi eligibilitas).
- **Columns:** `id`, `order_item_id` (FK, unique), `user_id` (FK), `product_id` (FK), `rating` (1–5), `comment`, `seller_reply`, `status` (visible/hidden/flagged).
- **Constraints:** Satu review per `order_item_id`.

### 2.26 `review_images`
- **Purpose:** Lampiran foto pada review.
- **Columns:** `id`, `review_id` (FK), `url`.

### 2.27 `promotions`
- **Purpose:** Kampanye diskon milik seller (mis. diskon persentase pada produk tertentu, periode tertentu).
- **Columns:** `id`, `store_id` (FK), `name`, `type` (percentage/fixed), `value`, `starts_at`, `ends_at`, `status`.
- **Relationships:** M—N ke `products` via `promotion_products`.

### 2.28 `promotion_products`
- **Purpose:** Pivot promosi↔produk.
- **Columns:** `promotion_id` (FK), `product_id` (FK).
- **Constraints:** Composite unique.

### 2.29 `coupons`
- **Purpose:** Kode kupon (platform-wide atau per-store) dengan aturan penggunaan.
- **Columns:** `id`, `store_id` (FK nullable = platform-wide), `code` (unique), `type`, `value`, `min_spend`, `usage_limit`, `starts_at`, `ends_at`, `status`.

### 2.30 `coupon_usages`
- **Purpose:** Mencatat siapa memakai kupon apa di order mana — mencegah reuse melebihi limit.
- **Columns:** `id`, `coupon_id` (FK), `user_id` (FK), `order_id` (FK), `used_at`.
- **Constraints:** Composite unique (`coupon_id`,`order_id`).

### 2.31 `notifications`
- **Purpose:** Notifikasi in-app untuk seluruh role (polymorphic recipient).
- **Columns:** `id`, `notifiable_id`, `notifiable_type` (polymorphic: `User`), `type`, `title`, `body`, `data` (JSON), `read_at`.
- **Indexes:** (`notifiable_id`,`notifiable_type`), `read_at`.

### 2.32 `seller_followers`
- **Purpose:** Customer mengikuti toko favorit.
- **Columns:** `id`, `user_id` (FK), `store_id` (FK).
- **Constraints:** Composite unique (`user_id`,`store_id`).

### 2.33 `reports`
- **Purpose:** Laporan yang diajukan user terhadap produk/toko/review (bahan moderasi admin).
- **Columns:** `id`, `reporter_id` (FK ke `users`), `reportable_id`, `reportable_type` (polymorphic: `Product`/`Store`/`Review`), `reason`, `description`, `status` (open/reviewed/dismissed/actioned).

### 2.34 `disputes`
- **Purpose:** Sengketa transaksi (mis. barang tidak sesuai) antara customer & seller, dimediasi admin.
- **Columns:** `id`, `sub_order_id` (FK), `raised_by` (FK ke `users`), `reason`, `status` (open/in_review/resolved/rejected), `resolution`, `resolved_by` (FK ke `users`, admin), `resolved_at`.
- **Relationships:** 1—N ke `dispute_messages`.

### 2.35 `dispute_messages`
- **Purpose:** Thread komunikasi dalam satu dispute (customer, seller, admin).
- **Columns:** `id`, `dispute_id` (FK), `sender_id` (FK), `message`, `attachment_url`.

### 2.36 `audit_logs`
- **Purpose:** Jejak audit aksi sensitif admin (mis. suspend user, ubah komisi, hapus produk).
- **Columns:** `id`, `actor_id` (FK ke `users`), `action`, `subject_id`, `subject_type`, `changes` (JSON), `ip_address`, `created_at`.
- **Indexes:** `actor_id`, `subject_type`+`subject_id`.

---

## 3. Mermaid ERD (Ringkas — Core Commerce Flow)

```mermaid
erDiagram
    USERS ||--o{ ADDRESSES : has
    USERS ||--o| SELLERS : "becomes"
    SELLERS ||--|| STORES : owns
    STORES ||--o{ PRODUCTS : lists
    CATEGORIES ||--o{ PRODUCTS : classifies
    PRODUCTS ||--o{ PRODUCT_IMAGES : has
    PRODUCTS ||--o{ PRODUCT_VARIANTS : has
    PRODUCT_VARIANTS ||--|| INVENTORIES : tracks
    USERS ||--|| CARTS : owns
    CARTS ||--o{ CART_ITEMS : contains
    CART_ITEMS }o--|| PRODUCT_VARIANTS : refers
    USERS ||--o{ ORDERS : places
    ORDERS ||--o{ SUB_ORDERS : "splits into"
    SUB_ORDERS }o--|| STORES : "belongs to"
    SUB_ORDERS ||--o{ ORDER_ITEMS : contains
    ORDER_ITEMS }o--|| PRODUCT_VARIANTS : refers
    ORDERS ||--|| PAYMENTS : "paid via"
    SUB_ORDERS ||--|| SHIPMENTS : "shipped via"
    ORDER_ITEMS ||--o| REVIEWS : "reviewed by"
    SUB_ORDERS ||--o{ DISPUTES : "may raise"
```

---

## 4. Naming Convention

- **Table name:** plural, `snake_case` → `products`, `sub_orders`, `product_attribute_values`.
- **Pivot table:** gabungan nama tabel singular urut alfabetis atau nama deskriptif eksplisit jika pivot punya kolom tambahan → `promotion_products`, `role_permissions`.
- **Column name:** `snake_case`, deskriptif, hindari singkatan ambigu → `verification_status` bukan `verif_stat`.
- **Foreign key:** `{singular_table}_id` → `product_id`, `store_id`, `sub_order_id`.
- **Boolean column:** prefix `is_`/`has_` → `is_default`, `is_primary`, `has_variants`.
- **Status column:** selalu `status` (string/enum), nilai dijelaskan di dokumentasi tabel masing-masing (§2), bukan angka magic number.

## 5. Primary Key Strategy

- Seluruh tabel menggunakan **auto-increment BIGINT UNSIGNED** sebagai primary key internal (`id`) untuk performa index & simplicity join.
- Untuk entity yang **ter-ekspos di URL publik** (mis. `orders.order_number`, `stores.slug`), digunakan kolom **tambahan** yang unik dan non-sequential (UUID/random string/slug) — bukan mengganti PK, agar internal ID tidak bocor ke publik namun query internal tetap efisien.

## 6. Foreign Key Strategy

- Seluruh FK didefinisikan dengan constraint eksplisit (`foreignId()->constrained()`).
- **On delete behavior** ditentukan per relasi:
  - Relasi yang **wajib ikut hilang** jika parent hilang (mis. `product_images` saat `products` dihapus permanen) → `cascade`.
  - Relasi yang **harus dipertahankan untuk histori** (mis. `order_items` terhadap `product_variants`) → **tidak cascade delete**; produk memakai **soft delete**, bukan hard delete, agar FK tetap valid.
  - Relasi opsional (mis. `promotion.store_id` saat kupon platform-wide) → `nullOnDelete()` jika kolom nullable.

## 7. Index Strategy

- Setiap FK otomatis diberi index (default Laravel).
- Kolom yang sering dipakai untuk filter/search diberi index tambahan: `products.status`, `orders.status`, `sub_orders.status`, `users.email` (unique), `stores.slug` (unique), `products.slug` (unique per store).
- Kolom pencarian teks (`products.name`) mempertimbangkan **full-text index** untuk mendukung fitur search.
- Composite index untuk query gabungan yang sering terjadi, mis. (`store_id`,`status`) pada `products` untuk query "produk aktif milik toko X".

## 8. Soft Delete Strategy

Soft delete (`deleted_at`) diterapkan pada entity yang **berdampak ke histori transaksi** jika dihapus keras:
```text
users, sellers, stores, products, categories
```
Entity murni operasional/log (mis. `notifications`, `audit_logs`, `shipment_tracking_events`) **tidak** memakai soft delete — dihapus keras jika memang perlu, atau di-retain permanen untuk audit log.

## 9. Timestamp Strategy

- Seluruh tabel memiliki `created_at` dan `updated_at` standar Laravel.
- Tabel dengan event waktu spesifik menambahkan kolom timestamp bernama jelas, bukan reuse `updated_at`: `email_verified_at`, `paid_at`, `shipped_at`, `delivered_at`, `resolved_at`, `verified_at`.

## 10. Status Fields

- Semua kolom status memakai tipe **string pendek dengan enum-like values** (didefinisikan di level aplikasi/constant, bukan native DB ENUM) agar mudah ditambah nilai baru tanpa migration ubah kolom.
- Setiap perubahan status pada entity kritikal (`orders`, `sub_orders`, `payments`, `disputes`) sebaiknya juga tercermin di `audit_logs` (untuk aksi admin) atau tabel event khusus (`shipment_tracking_events` untuk shipment) — bukan hanya overwrite kolom status tanpa jejak.

## 11. Data Normalization

- Skema dinormalisasi hingga **3NF** untuk data master (users, products, categories, dst.).
- **Denormalisasi disengaja** dilakukan pada data transaksional historis (`order_items.price_snapshot`, `order_items.product_name_snapshot`) — ini bukan pelanggaran normalisasi, melainkan snapshot yang memang secara bisnis harus immutable terhadap perubahan master data di kemudian hari.

## 12. Data Integrity

- Constraint uniqueness diterapkan di level database (bukan hanya validasi aplikasi): `users.email`, `stores.slug`, `product_variants.sku`, `coupons.code`.
- Constraint composite unique untuk mencegah duplikasi relasi: `(cart_id, product_variant_id)`, `(wishlist_id, product_id)`, `(role_id, permission_id)`, `(user_id, store_id)` pada `seller_followers`.
- Validasi nilai (mis. `reviews.rating` antara 1–5) diterapkan di level aplikasi (Form Request) — didokumentasikan bersama endpoint terkait di `API.md`.

## 13. Audit Strategy

- `audit_logs` mencatat seluruh **aksi admin sensitif**: perubahan status user/seller, penghapusan produk oleh admin, keputusan dispute, perubahan pengaturan platform.
- Format `changes` disimpan sebagai JSON berisi before/after value untuk field yang berubah, memudahkan investigasi.
- `shipment_tracking_events` berfungsi sebagai audit trail khusus untuk perjalanan pengiriman, dipisah dari `audit_logs` karena sifatnya berbeda (event eksternal dari courier, bukan aksi user internal).

## 14. Migration Ordering (Panduan untuk Fase Development)

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

## 15. Catatan Desain Skema

- **Split order→sub_order** dipilih agar setiap seller punya unit kerja independen (status, shipment, dispute) tanpa saling mengunci data order induk.
- **Snapshot fields** (`price_snapshot`, `product_name_snapshot`, dst.) sengaja didenormalisasi di `order_items` agar riwayat transaksi tidak berubah walau produk/harga di master data berubah kemudian.
- **Polymorphic tables** (`addresses`, `notifications`, `reports`) dipakai untuk entity yang secara natural dipakai lintas beberapa model, menghindari duplikasi tabel serupa.
- Semua tabel transaksional inti memakai `id` bertipe BIGINT auto-increment (lihat §5), dengan `created_at`/`updated_at` standar, dan `deleted_at` (soft delete) pada entity yang berdampak ke histori (`users`, `products`, `stores`).
