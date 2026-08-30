# ROUTES.md — Route & Page Specification

Dokumen ini memetakan **route** per surface ke **spesifikasi halaman** (purpose, main sections, CTA, key components, data, actions, states, responsive behavior) dan ke **design image** acuan di `docs/stitch_pazarz_ui_design/`. Token visual & komponen yang disebut di bawah merujuk pada `DESIGN.md`. Jika suatu route memiliki gambar desain final di `docs/stitch_pazarz_ui_design/`, gambar tersebut adalah **visual target** — spesifikasi teks di sini adalah pelengkap (lihat `DECISIONS.md` §Conflict Rule).

Konvensi spesifikasi per halaman: **Purpose · Main Sections · Primary CTA · Secondary CTA · Key Components · Data Required · Actions · States · Responsive Behavior · Design Reference.**

---

## A. CUSTOMER FRONTEND (React) — `/`

```text
/
/products
/products/:slug
/categories/:slug
/search
/cart
/checkout
/account
/account/orders
/account/orders/:id
/account/addresses
/account/profile
/login
/register
```

### A1. `/` — Landing
- **Design reference:** `docs/stitch_pazarz_ui_design/frontend_ui/landing_page_pazarz/screen.png`
- **Purpose:** Entry point utama, membangun kepercayaan & mendorong discovery.
- **Main sections:** Hero full-bleed (Display headline + CTA), strip kategori unggulan, "Trending" card grid, section brand/value proposition, featured products grid, footer.
- **Primary CTA:** "Belanja Sekarang" → Browse.
- **Secondary CTA:** "Lihat Semua" per section.
- **Key components:** Hero, Product Card, kategori chip, Header/Footer.
- **Data:** kategori unggulan, produk trending/featured (curated/algoritmik).
- **Actions:** klik kategori → Category page; klik produk → Product Detail.
- **States:** Loading (skeleton hero+grid), Empty (fallback ke kategori statis jika data trending kosong), Error (retry banner tanpa memblokir seluruh halaman).
- **Responsive:** Hero height menyesuaikan (`DESIGN.md` §10), grid 4→3→2 kolom.

### A2. `/products`, `/categories/:slug` — Browse / Category
- **Design reference:** `docs/stitch_pazarz_ui_design/frontend_ui/browse_products_pazarz/screen.png`
- **Purpose:** Discovery produk berdasarkan kategori/listing umum.
- **Main sections:** Breadcrumb, filter sidebar (kategori, harga, rating, brand/toko), grid produk, sort control, pagination.
- **Primary CTA:** "Tambah ke Keranjang" (quick-add di card, opsional) / klik card → detail.
- **Secondary CTA:** "Reset Filter".
- **Key components:** Filter Bar, Product Card grid, Pagination.
- **Data:** daftar produk (paginated), facet filter (kategori/harga range/rating).
- **Actions:** apply filter, ubah sort, quick-add wishlist (icon heart pada card).
- **States:** Loading (skeleton grid), Empty ("Produk tidak ditemukan" + reset filter CTA), Error, Success (filter applied → grid update).
- **Responsive:** Filter → drawer bottom di mobile; grid 4→3→2.

### A3. `/search` — Search Results
- **Design reference:** sama pola dengan A2 (`docs/stitch_pazarz_ui_design/frontend_ui/browse_products_pazarz/screen.png`).
- Sama struktur dengan A2, ditambah: search bar aktif dengan query terlihat, suggestion "Mungkin maksud Anda" jika hasil tipis, empty state khusus ("Tidak ada hasil untuk '{query}'").

### A4. `/products/:slug` — Product Detail
- **Design reference:** `docs/stitch_pazarz_ui_design/frontend_ui/product_detail_pazarz/screen.png`
- **Purpose:** Konversi — memberi info cukup untuk keputusan beli.
- **Main sections:** Gallery, info produk (nama, harga, rating, seller mini-card), pemilih varian (chip warna/ukuran), quantity selector, CTA area, tab (Deskripsi/Spesifikasi/Review), produk terkait.
- **Primary CTA:** "Tambah ke Keranjang" (sticky di mobile).
- **Secondary CTA:** "Beli Langsung" (checkout langsung), "Wishlist" (icon), "Chat Seller" (future — lihat `FEATURES.md`).
- **Key components:** Product Gallery, Price, Rating, Seller Card, Tabs, Review list.
- **Data:** detail produk+varian+stok per varian, review & rating summary, produk terkait/toko lain.
- **Actions:** pilih varian (update harga/gambar/stok tersedia), ubah quantity (dibatasi stok), add to cart, add to wishlist.
- **States:** Loading (skeleton gallery+info), Error (404 produk tidak ada/nonaktif), varian out-of-stock (CTA disabled + label "Stok Habis"), Success (toast "Ditambahkan ke keranjang").
- **Responsive:** 2 kolom desktop (gallery kiri, info kanan sticky) → stack mobile, CTA sticky bottom mobile.

### A5. `/stores/:slug` — Seller/Store Detail
- **Purpose:** Etalase toko, membangun kepercayaan terhadap seller.
- **Main sections:** Banner + info toko (rating, jumlah produk, follower), tab (Produk/Tentang/Review), grid produk toko.
- **Primary CTA:** "Follow Toko".
- **Secondary CTA:** "Chat" (future).
- **Data:** profil store, rating agregat, daftar produk toko.
- **States:** standar (Loading/Empty produk kosong/Error).
- **Responsive:** banner height menyesuaikan, grid produk 4→2.

### A6. `/cart` — Cart
- **Design reference:** `docs/stitch_pazarz_ui_design/frontend_ui/shopping_cart_pazarz/screen.png`
- **Purpose:** Review & edit item sebelum checkout, dikelompokkan per seller.
- **Main sections:** List item per-toko (grouped), checkbox pilih item, summary card (subtotal, estimasi, tombol checkout).
- **Primary CTA:** "Checkout" (item terpilih).
- **Secondary CTA:** "Hapus", "Simpan ke Wishlist".
- **Key components:** Cart Item, Checkout Summary.
- **Data:** isi cart dengan harga real-time & status stok.
- **Actions:** update quantity, hapus item, pilih/batalkan pilihan item.
- **States:** Empty ("Keranjang kosong" + CTA "Mulai Belanja"), item stok berubah (badge warning "Stok berkurang, sisa X"), Loading saat update quantity.
- **Responsive:** summary → sticky bottom bar mobile.

### A7. `/checkout` — Checkout
- **Design reference:** `docs/stitch_pazarz_ui_design/frontend_ui/checkout_pazarz/screen.png`
- **Purpose:** Menyelesaikan transaksi dengan minim friksi.
- **Main sections:** Alamat pengiriman (pilih/tambah), metode pengiriman per toko, kupon/promo, ringkasan pembayaran, metode pembayaran.
- **Primary CTA:** "Bayar Sekarang".
- **Key components:** Address selector, Checkout Summary, form kupon.
- **Data:** daftar alamat user, opsi ongkir per sub-order (dari courier API), metode pembayaran tersedia.
- **Actions:** pilih alamat, pilih kirim per toko, apply kupon, pilih metode bayar, submit (`POST /checkout` — lihat `API.md` §7.8).
- **States:** Loading (kalkulasi ongkir), Error (kupon invalid → inline error, bukan blocking seluruh form), stok berubah saat checkout (409 → modal "beberapa item tidak tersedia, silakan review kembali").
- **Responsive:** step tetap 1 kolom di semua breakpoint, summary sticky bottom di mobile.

### A8. Payment Status
- **Design reference:** `docs/stitch_pazarz_ui_design/frontend_ui/order_status_pazarz/screen.png`
- **Purpose:** Konfirmasi hasil pembayaran / instruksi pembayaran tertunda (mis. VA number).
- **Main sections:** status icon besar, detail instruksi (jika pending), ringkasan order.
- **Primary CTA:** "Lihat Pesanan" (jika sukses) / "Cek Status" (jika pending, polling ringan).
- **States:** Pending, Success, Failed (dengan CTA "Coba Lagi").

### A9. `/account/orders` — Orders (List)
- **Design reference:** `docs/stitch_pazarz_ui_design/frontend_ui/your_orders_pazarz/screen.png`
- **Purpose:** Riwayat & tracking seluruh pesanan.
- **Main sections:** Tab filter status (Semua/Diproses/Dikirim/Selesai/Dibatalkan), list order card.
- **Key components:** Order Card dengan status badge.
- **Data:** daftar order milik user, ringkasan status per sub-order (`GET /orders` — `API.md` §7.10).
- **States:** Empty per tab ("Belum ada pesanan {status}"), Loading skeleton list.
- **Responsive:** list tetap 1 kolom di semua breakpoint (natural untuk konten ini).

### A10. `/account/orders/:id` — Order Detail
- **Main sections:** Timeline status (stepper), info sub-order per toko, item list, alamat & metode bayar, tombol aksi kontekstual.
- **Primary CTA:** kontekstual — "Konfirmasi Diterima" (jika delivered), "Beri Review" (jika completed), "Ajukan Komplain" (jika delivered/completed dalam window waktu tertentu).
- **Data:** detail order+sub_order+shipment tracking events (`GET /orders/{order_number}` — `API.md` §7.11).
- **States:** menyesuaikan status order (`USER-FLOW.md` §2.1 state diagram) — setiap status menampilkan timeline & aksi yang relevan saja.

### A11. Wishlist
- **Main sections:** Grid produk tersimpan.
- **Primary CTA:** "Tambah ke Keranjang" per item.
- **States:** Empty ("Wishlist kosong").

### A12. `/account/profile`, `/account/addresses` — Profile & Settings
- **Design reference:** `docs/stitch_pazarz_ui_design/frontend_ui/account_settings_pazarz/screen.png`
- **Main sections:** Sub-nav (Info Akun, Alamat, Keamanan, Notifikasi), form per section.
- **Primary CTA:** "Simpan Perubahan".
- **Data:** profil user, daftar alamat.
- **States:** Success (toast tersimpan), Error validasi inline per field.

### A13. `/login`, `/register`
- **Main sections:** Form login/register (email, password), link lupa password, link antar login↔register.
- **Primary CTA:** "Masuk" / "Daftar".
- **States:** Error kredensial salah, Loading saat submit, Success → redirect (lihat `USER-FLOW.md` §1.1).

---

## B. SELLER DASHBOARD (Laravel Blade) — `/seller`

```text
/seller
/seller/products
/seller/products/create
/seller/products/{product}/edit
/seller/inventory
/seller/orders
/seller/orders/{order}
```

### B1. `/seller` — Dashboard (Home)
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/seller/seller_overview_pazarz_dashboard/screen.png`
- **Main sections:** Metric card (Revenue hari ini/bulan ini, Order baru, Produk low-stock, Rating toko), chart penjualan (line, filter periode), activity feed order terbaru.
- **Data:** agregat penjualan, daftar order terbaru.
- **States:** Empty (toko baru, belum ada data → tampilkan panduan onboarding singkat), Loading skeleton chart+card.

### B2. `/seller/products` — Products (List)
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/seller/products_pazarz_seller_dashboard/screen.png`
- **Main sections:** Filter bar (status, kategori), data table (thumbnail, nama, harga, stok, status, aksi).
- **Primary CTA:** "Tambah Produk".
- **Secondary CTA:** bulk action (aktif/nonaktifkan terpilih).
- **States:** Empty ("Belum ada produk" + CTA tambah), Loading skeleton table.

### B3. `/seller/products/create`, `/seller/products/{product}/edit` — Create/Edit Product
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/seller/add_product_pazarz_seller_dashboard/screen.png`
- **Main sections:** Form multi-section (Info Dasar, Kategori & Atribut, Varian & Harga, Gambar, Stok, Pengiriman/Berat).
- **Primary CTA:** "Simpan & Publikasikan".
- **Secondary CTA:** "Simpan sebagai Draft".
- **Data:** kategori & atribut tersedia (dari master data admin).
- **States:** validasi inline per section, Success (redirect ke list + toast). Lihat alur lengkap di `USER-FLOW.md` §4.

### B4. `/seller/inventory` — Inventory
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/seller/inventory_pazarz_seller_dashboard/screen.png`
- **Main sections:** Table stok per varian across produk, filter low-stock.
- **Primary CTA:** inline edit quantity (Save per baris atau bulk save).
- **States:** highlight baris dengan stok di bawah `low_stock_threshold` (`DATABASE.md` §2.14).

### B5. `/seller/orders` — Orders (Sub-Orders List)
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/seller/manage_orders_seller_dashboard/screen.png`
- **Main sections:** Tab status, table sub-order (order#, customer, item count, total, status, aksi).
- **Primary CTA:** kontekstual per baris ("Konfirmasi", "Input Resi").
- **States:** Empty per tab, badge status warna semantic.

### B6. `/seller/orders/{order}` — Order Detail
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/seller/order_details_seller_dashboard/screen.png`
- **Main sections:** Info customer & alamat kirim, item list, area aksi (konfirmasi/batalkan/input resi), riwayat status.
- **Primary CTA:** kontekstual sesuai status (lihat `USER-FLOW.md` §5 Seller Flow).

### B7. Promotions & Coupons
- **Main sections:** List promosi/kupon aktif & terjadwal, form buat baru (pilih produk, tipe diskon, periode).
- **Primary CTA:** "Buat Promosi" / "Buat Kupon".
- **States:** Empty, badge status (aktif/terjadwal/berakhir).

### B8. Reviews
- **Main sections:** List review masuk (filter rating), area balas review.
- **Primary CTA:** "Balas" per review.

### B9. Analytics
- **Main sections:** Chart revenue trend, produk terlaris, funnel sederhana (views→cart→order jika data tersedia).
- **Data:** agregat historis penjualan.

### B10. Store Settings
- **Main sections:** Profil toko (logo/banner/deskripsi), alamat toko, pengaturan pengiriman default, jam operasional (untuk respons chat, jika ada).
- **Primary CTA:** "Simpan Perubahan".

---

## C. ADMIN DASHBOARD (Laravel Blade) — `/admin`

```text
/admin
/admin/users
/admin/users/{user}
/admin/sellers
/admin/sellers/{seller}
/admin/categories
/admin/products
/admin/products/{product}
/admin/orders
```

### C1. `/admin` — Dashboard (Home)
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/admin/platform_overview_pazarz_admin_dashboard/screen.png`
- **Main sections:** Metric platform-wide (GMV, order hari ini, seller pending, dispute terbuka, laporan baru), chart tren, shortcut ke antrian aksi (verifikasi seller, moderasi).
- **States:** Loading skeleton, badge angka pada shortcut jika ada item pending.

### C2. `/admin/users` — Users
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/admin/user_management_admin_dashboard/screen.png`
- **Main sections:** Table user (nama, email, role, status, tanggal daftar), filter & search.
- **Primary CTA:** aksi per baris ("Suspend", "Aktifkan").
- **States:** konfirmasi modal sebelum suspend (aksi sensitif → tercatat di `audit_logs`).

### C3. `/admin/sellers` — Sellers / Seller Verification
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/admin/seller_verification_admin_dashboard/screen.png`
- **Main sections:** Tab (Pending/Verified/Rejected), card/table aplikasi seller dengan preview dokumen.
- **Primary CTA:** "Approve" / "Reject" (dengan field alasan wajib untuk reject).
- **States:** Empty tab, Loading dokumen preview.

### C4. Stores
- **Main sections:** Table toko, filter status, aksi nonaktifkan toko (override).

### C5. `/admin/products` — Products (Moderation)
- **Main sections:** Table produk lintas toko, filter status/flagged, aksi moderasi (nonaktifkan produk melanggar).
- **Primary CTA:** "Nonaktifkan Produk" (dengan alasan).

### C6. `/admin/categories` — Categories & Attributes
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/admin/categories_attributes_admin_dashboard/screen.png`
- **Main sections:** Tree kategori (drag reorder opsional), form tambah/edit kategori & atribut global.
- **Primary CTA:** "Tambah Kategori".

### C7. `/admin/orders` — Orders (Monitoring)
- **Main sections:** Table order/sub-order platform-wide, filter status/tanggal/toko, detail read-only (admin tidak mengubah operasional order langsung kecuali override khusus).

### C8. Payments
- **Main sections:** Table transaksi pembayaran, filter status/provider, detail transaksi untuk investigasi.

### C9. Reviews (Moderation)
- **Main sections:** Queue review yang di-flag/report, aksi (Hide/Restore/Warn Seller).

### C10. Reports (User Reports)
- **Main sections:** Queue laporan dari user terhadap produk/toko/review, detail & aksi tindak lanjut.

### C11. Disputes
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/admin/dispute_resolution_admin_dashboard/screen.png`
- **Main sections:** List dispute (kiri) + thread percakapan (kanan) — pola inbox.
- **Primary CTA:** "Selesaikan — Refund Customer" / "Selesaikan — Tolak Klaim".
- **States:** filter status dispute, badge urgensi (mis. sudah lewat SLA).

### C12. Promotions (Platform-wide)
- **Main sections:** List kupon platform, form buat kupon platform-wide.

### C13. Analytics & Reports
- **Main sections:** Dashboard analitik lanjutan (GMV trend, top category, top seller, retention ringkas), filter tanggal custom, export.

### C14. Audit Logs
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/admin/audit_logs_admin_dashboard/screen.png`
- **Main sections:** Table log aksi admin (actor, aksi, subjek, waktu), filter per actor/tanggal/tipe aksi, detail expand (before/after JSON — lihat `DATABASE.md` §2.36).

### C15. Platform Settings
- **Design reference:** `docs/stitch_pazarz_ui_design/backend_ui/admin/platform_settings_admin_dashboard/screen.png`
- **Main sections:** Pengaturan umum (komisi default, kebijakan dispute window, konfigurasi notifikasi), role & permission management (`ARCHITECTURE.md` §7).
- **Primary CTA:** "Simpan Perubahan" (aksi ini tercatat di `audit_logs`).

---

## D. Cross-Cutting UI Rules

- Setiap **data table** (Seller/Admin) wajib memiliki: loading skeleton, empty state, pagination, dan minimal satu filter relevan — tidak ada table "polos" tanpa state handling.
- Setiap **aksi destructive/sensitif** (suspend user, reject seller, nonaktifkan produk, resolve dispute) wajib melalui **confirmation modal** dan tercatat di `audit_logs` (untuk aksi admin).
- Setiap **form** memiliki validasi inline per field (bukan hanya summary error di atas form) — konsisten dengan `DESIGN.md` §6.1.
- Konsistensi status badge warna di seluruh surface: `pending/processing`=warning, `success/completed/verified`=success, `cancelled/rejected/error`=error, `info/default`=info — merujuk `DESIGN.md` §2.
