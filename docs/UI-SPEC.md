# UI-SPEC.md — Full Page & UI Specification

Konvensi per halaman: **Purpose · Main Sections · Primary CTA · Secondary CTA · Key Components · Data Required · Actions · States · Responsive Behavior.** Token visual & komponen merujuk pada `DESIGN.md`.

---

## A. CUSTOMER FRONTEND (React)

### A1. Landing
- **Purpose:** Entry point utama, membangun kepercayaan & mendorong discovery.
- **Main sections:** Hero full-bleed (Display headline + CTA), strip kategori unggulan, "Trending" card grid, section brand/value proposition, featured products grid, footer.
- **Primary CTA:** "Belanja Sekarang" → Browse.
- **Secondary CTA:** "Lihat Semua" per section.
- **Key components:** Hero, Product Card, kategori chip, Header/Footer.
- **Data:** kategori unggulan, produk trending/featured (curated/algoritmik).
- **Actions:** klik kategori → Category page; klik produk → Product Detail.
- **States:** Loading (skeleton hero+grid), Empty (fallback ke kategori statis jika data trending kosong), Error (retry banner tanpa memblokir seluruh halaman).
- **Responsive:** Hero height menyesuaikan (§10 DESIGN.md), grid 4→3→2 kolom.

### A2. Browse / Category
- **Purpose:** Discovery produk berdasarkan kategori/listing umum.
- **Main sections:** Breadcrumb, filter sidebar (kategori, harga, rating, brand/toko), grid produk, sort control, pagination.
- **Primary CTA:** "Tambah ke Keranjang" (quick-add di card, opsional) / klik card → detail.
- **Secondary CTA:** "Reset Filter".
- **Key components:** Filter Bar, Product Card grid, Pagination.
- **Data:** daftar produk (paginated), facet filter (kategori/harga range/rating).
- **Actions:** apply filter, ubah sort, quick-add wishlist (icon heart pada card).
- **States:** Loading (skeleton grid), Empty ("Produk tidak ditemukan" + reset filter CTA), Error, Success (filter applied → grid update).
- **Responsive:** Filter → drawer bottom di mobile; grid 4→3→2.

### A3. Search Results
- Sama struktur dengan A2, ditambah: search bar aktif dengan query terlihat, suggestion "Mungkin maksud Anda" jika hasil tipis, empty state khusus ("Tidak ada hasil untuk '{query}'").

### A4. Product Detail
- **Purpose:** Konversi — memberi info cukup untuk keputusan beli.
- **Main sections:** Gallery, info produk (nama, harga, rating, seller mini-card), pemilih varian (chip warna/ukuran), quantity selector, CTA area, tab (Deskripsi/Spesifikasi/Review), produk terkait.
- **Primary CTA:** "Tambah ke Keranjang" (sticky di mobile).
- **Secondary CTA:** "Beli Langsung" (checkout langsung), "Wishlist" (icon), "Chat Seller" (opsional MVP+).
- **Key components:** Product Gallery, Price, Rating, Seller Card, Tabs, Review list.
- **Data:** detail produk+varian+stok per varian, review & rating summary, produk terkait/toko lain.
- **Actions:** pilih varian (update harga/gambar/stok tersedia), ubah quantity (dibatasi stok), add to cart, add to wishlist.
- **States:** Loading (skeleton gallery+info), Error (404 produk tidak ada/nonaktif), varian out-of-stock (CTA disabled + label "Stok Habis"), Success (toast "Ditambahkan ke keranjang").
- **Responsive:** 2 kolom desktop (gallery kiri, info kanan sticky) → stack mobile, CTA sticky bottom mobile.

### A5. Seller/Store Detail
- **Purpose:** Etalase toko, membangun kepercayaan terhadap seller.
- **Main sections:** Banner + info toko (rating, jumlah produk, follower), tab (Produk/Tentang/Review), grid produk toko.
- **Primary CTA:** "Follow Toko".
- **Secondary CTA:** "Chat" (opsional).
- **Data:** profil store, rating agregat, daftar produk toko.
- **States:** standar (Loading/Empty produk kosong/Error).
- **Responsive:** banner height menyesuaikan, grid produk 4→2.

### A6. Cart
- **Purpose:** Review & edit item sebelum checkout, dikelompokkan per seller.
- **Main sections:** List item per-toko (grouped), checkbox pilih item, summary card (subtotal, estimasi, tombol checkout).
- **Primary CTA:** "Checkout" (item terpilih).
- **Secondary CTA:** "Hapus", "Simpan ke Wishlist".
- **Key components:** Cart Item, Checkout Summary.
- **Data:** isi cart dengan harga real-time & status stok.
- **Actions:** update quantity, hapus item, pilih/batalkan pilihan item.
- **States:** Empty ("Keranjang kosong" + CTA "Mulai Belanja"), item stok berubah (badge warning "Stok berkurang, sisa X"), Loading saat update quantity.
- **Responsive:** summary → sticky bottom bar mobile.

### A7. Checkout
- **Purpose:** Menyelesaikan transaksi dengan minim friksi.
- **Main sections:** Alamat pengiriman (pilih/tambah), metode pengiriman per toko, kupon/promo, ringkasan pembayaran, metode pembayaran.
- **Primary CTA:** "Bayar Sekarang".
- **Key components:** Address selector, Checkout Summary, form kupon.
- **Data:** daftar alamat user, opsi ongkir per sub-order (dari courier API), metode pembayaran tersedia.
- **Actions:** pilih alamat, pilih kirim per toko, apply kupon, pilih metode bayar, submit.
- **States:** Loading (kalkulasi ongkir), Error (kupon invalid → inline error, bukan blocking seluruh form), stok berubah saat checkout (409 → modal "beberapa item tidak tersedia, silakan review kembali").
- **Responsive:** step tetap 1 kolom di semua breakpoint, summary sticky bottom di mobile.

### A8. Payment Status
- **Purpose:** Konfirmasi hasil pembayaran / instruksi pembayaran tertunda (mis. VA number).
- **Main sections:** status icon besar, detail instruksi (jika pending), ringkasan order.
- **Primary CTA:** "Lihat Pesanan" (jika sukses) / "Cek Status" (jika pending, polling ringan).
- **States:** Pending, Success, Failed (dengan CTA "Coba Lagi").

### A9. Orders (List)
- **Purpose:** Riwayat & tracking seluruh pesanan.
- **Main sections:** Tab filter status (Semua/Diproses/Dikirim/Selesai/Dibatalkan), list order card.
- **Key components:** Order Card dengan status badge.
- **Data:** daftar order milik user, ringkasan status per sub-order.
- **States:** Empty per tab ("Belum ada pesanan {status}"), Loading skeleton list.
- **Responsive:** list tetap 1 kolom di semua breakpoint (natural untuk konten ini).

### A10. Order Detail
- **Main sections:** Timeline status (stepper), info sub-order per toko, item list, alamat & metode bayar, tombol aksi kontekstual.
- **Primary CTA:** kontekstual — "Konfirmasi Diterima" (jika delivered), "Beri Review" (jika completed), "Ajukan Komplain" (jika delivered/completed dalam window waktu tertentu).
- **Data:** detail order+sub_order+shipment tracking events.
- **States:** menyesuaikan status order (§FLOW.md state diagram) — setiap status menampilkan timeline & aksi yang relevan saja.

### A11. Wishlist
- **Main sections:** Grid produk tersimpan.
- **Primary CTA:** "Tambah ke Keranjang" per item.
- **States:** Empty ("Wishlist kosong").

### A12. Profile & Settings
- **Main sections:** Sub-nav (Info Akun, Alamat, Keamanan, Notifikasi), form per section.
- **Primary CTA:** "Simpan Perubahan".
- **Data:** profil user, daftar alamat.
- **States:** Success (toast tersimpan), Error validasi inline per field.

---

## B. SELLER DASHBOARD (Laravel Blade)

### B1. Dashboard (Home)
- **Main sections:** Metric card (Revenue hari ini/bulan ini, Order baru, Produk low-stock, Rating toko), chart penjualan (line, filter periode), activity feed order terbaru.
- **Data:** agregat penjualan, daftar order terbaru.
- **States:** Empty (toko baru, belum ada data → tampilkan panduan onboarding singkat), Loading skeleton chart+card.

### B2. Products (List)
- **Main sections:** Filter bar (status, kategori), data table (thumbnail, nama, harga, stok, status, aksi).
- **Primary CTA:** "Tambah Produk".
- **Secondary CTA:** bulk action (aktif/nonaktifkan terpilih).
- **States:** Empty ("Belum ada produk" + CTA tambah), Loading skeleton table.

### B3. Create/Edit Product
- **Main sections:** Form multi-section (Info Dasar, Kategori & Atribut, Varian & Harga, Gambar, Stok, Pengiriman/Berat).
- **Primary CTA:** "Simpan & Publikasikan".
- **Secondary CTA:** "Simpan sebagai Draft".
- **Data:** kategori & atribut tersedia (dari master data admin).
- **States:** validasi inline per section, Success (redirect ke list + toast).

### B4. Inventory
- **Main sections:** Table stok per varian across produk, filter low-stock.
- **Primary CTA:** inline edit quantity (Save per baris atau bulk save).
- **States:** highlight baris dengan stok di bawah `low_stock_threshold`.

### B5. Orders (Sub-Orders List)
- **Main sections:** Tab status, table sub-order (order#, customer, item count, total, status, aksi).
- **Primary CTA:** kontekstual per baris ("Konfirmasi", "Input Resi").
- **States:** Empty per tab, badge status warna semantic.

### B6. Order Detail
- **Main sections:** Info customer & alamat kirim, item list, area aksi (konfirmasi/batalkan/input resi), riwayat status.
- **Primary CTA:** kontekstual sesuai status (lihat FLOW.md § Seller Flow).

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

## C. ADMIN DASHBOARD (Laravel Blade)

### C1. Dashboard (Home)
- **Main sections:** Metric platform-wide (GMV, order hari ini, seller pending, dispute terbuka, laporan baru), chart tren, shortcut ke antrian aksi (verifikasi seller, moderasi).
- **States:** Loading skeleton, badge angka pada shortcut jika ada item pending.

### C2. Users
- **Main sections:** Table user (nama, email, role, status, tanggal daftar), filter & search.
- **Primary CTA:** aksi per baris ("Suspend", "Aktifkan").
- **States:** konfirmasi modal sebelum suspend (aksi sensitif → tercatat di audit log).

### C3. Sellers / Seller Verification
- **Main sections:** Tab (Pending/Verified/Rejected), card/table aplikasi seller dengan preview dokumen.
- **Primary CTA:** "Approve" / "Reject" (dengan field alasan wajib untuk reject).
- **States:** Empty tab, Loading dokumen preview.

### C4. Stores
- **Main sections:** Table toko, filter status, aksi nonaktifkan toko (override).

### C5. Products (Moderation)
- **Main sections:** Table produk lintas toko, filter status/flagged, aksi moderasi (nonaktifkan produk melanggar).
- **Primary CTA:** "Nonaktifkan Produk" (dengan alasan).

### C6. Categories & Attributes
- **Main sections:** Tree kategori (drag reorder opsional), form tambah/edit kategori & atribut global.
- **Primary CTA:** "Tambah Kategori".

### C7. Orders (Monitoring)
- **Main sections:** Table order/sub-order platform-wide, filter status/tanggal/toko, detail read-only (admin tidak mengubah operasional order langsung kecuali override khusus).

### C8. Payments
- **Main sections:** Table transaksi pembayaran, filter status/provider, detail transaksi untuk investigasi.

### C9. Reviews (Moderation)
- **Main sections:** Queue review yang di-flag/report, aksi (Hide/Restore/Warn Seller).

### C10. Reports (User Reports)
- **Main sections:** Queue laporan dari user terhadap produk/toko/review, detail & aksi tindak lanjut.

### C11. Disputes
- **Main sections:** List dispute (kiri) + thread percakapan (kanan) — pola inbox.
- **Primary CTA:** "Selesaikan — Refund Customer" / "Selesaikan — Tolak Klaim".
- **States:** filter status dispute, badge urgensi (mis. sudah lewat SLA).

### C12. Promotions (Platform-wide)
- **Main sections:** List kupon platform, form buat kupon platform-wide.

### C13. Analytics & Reports
- **Main sections:** Dashboard analitik lanjutan (GMV trend, top category, top seller, retention ringkas), filter tanggal custom, export.

### C14. Audit Logs
- **Main sections:** Table log aksi admin (actor, aksi, subjek, waktu), filter per actor/tanggal/tipe aksi, detail expand (before/after JSON).

### C15. Platform Settings
- **Main sections:** Pengaturan umum (komisi default, kebijakan dispute window, konfigurasi notifikasi), role & permission management (§ROLES.md).
- **Primary CTA:** "Simpan Perubahan" (aksi ini tercatat di audit log).

---

## D. Cross-Cutting UI Rules

- Setiap **data table** (Seller/Admin) wajib memiliki: loading skeleton, empty state, pagination, dan minimal satu filter relevan — tidak ada table "polos" tanpa state handling.
- Setiap **aksi destructive/sensitif** (suspend user, reject seller, nonaktifkan produk, resolve dispute) wajib melalui **confirmation modal** dan tercatat di `audit_logs` (untuk aksi admin).
- Setiap **form** memiliki validasi inline per field (bukan hanya summary error di atas form) — konsisten dengan §6.1 `DESIGN.md`.
- Konsistensi status badge warna di seluruh surface: `pending/processing`=warning, `success/completed/verified`=success, `cancelled/rejected/error`=error, `info/default`=info — merujuk §2 `DESIGN.md`.
