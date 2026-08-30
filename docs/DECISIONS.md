# DECISIONS.md — Architectural Decisions

Dokumen ini mencatat keputusan arsitektur & desain penting untuk Pazarz, beserta alasan dan trade-off-nya. Untuk detail teknis lengkap, lihat dokumen yang direferensikan pada setiap entri.

---

## 1. React Customer + Blade Admin/Seller (Hybrid Architecture)

- **Keputusan:** Customer-facing web wajib React + Vite; Seller & Admin dashboard wajib Laravel Blade.
- **Alasan:** Customer butuh UX interaktif tingkat SPA (filter real-time, cart, checkout dinamis) yang cocok dengan React. Seller/Admin adalah tooling operasional data-dense yang cukup dilayani server-rendered Blade tanpa kompleksitas SPA tambahan, dan lebih cepat dikembangkan untuk kebutuhan CRUD/dashboard.
- **Trade-off:** Dua paradigma frontend berjalan paralel (butuh disiplin agar business logic tidak terduplikasi) — dimitigasi dengan menempatkan seluruh logic di Service/Action layer Laravel (`ARCHITECTURE.md` §2).
- **Status:** Final. Tidak boleh diubah tanpa instruksi eksplisit.

## 2. Laravel sebagai API + Web Application Sekaligus

- **Keputusan:** Satu aplikasi Laravel melayani REST API (`routes/api.php`) untuk React dan web routes (`routes/web.php`) untuk Blade — bukan dua backend terpisah.
- **Alasan:** Menjamin satu sumber data & business logic yang konsisten real-time antar dashboard, menghindari sinkronisasi data antar service yang kompleks pada tahap ini.
- **Referensi:** `ARCHITECTURE.md` §2, §6.

## 3. Multi-Vendor Order Model: Parent Order + Sub-Orders

- **Keputusan:** Satu transaksi checkout customer menghasilkan satu `order` (satu pembayaran), dipecah menjadi beberapa `sub_orders` (satu per seller yang terlibat).
- **Alasan:** Memberi setiap seller unit kerja independen (status, shipment, dispute) tanpa saling mengunci data order induk atau seller lain; customer tetap mengalami satu transaksi/pembayaran tunggal.
- **Trade-off:** Kompleksitas tambahan pada kalkulasi refund parsial dan rekonsiliasi pembayaran vs sub-order — dimitigasi dengan snapshot & status terpisah per sub-order.
- **Referensi:** `DATABASE.md` §2.19–2.21, §15; `USER-FLOW.md` §2–3.

## 4. Payment Abstraction Sebelum Payment Gateway Nyata

- **Keputusan:** Business logic pembayaran (status, webhook, refund) dirancang sebagai abstraksi (`payments` table + service layer) yang tidak terikat pada satu provider gateway spesifik.
- **Alasan:** Memungkinkan penggantian/penambahan provider payment gateway di masa depan tanpa mengubah struktur inti order/payment.
- **Referensi:** `PRD.md` §9; `DATABASE.md` §2.22; `API.md` §7.9.

## 5. API Contract Sebagai Kontrak Formal antara React dan Laravel

- **Keputusan:** Seluruh komunikasi Customer↔Backend melalui REST API versioned (`/api/v1/`) dengan format response konsisten (`success/data/meta` atau `success/error`), didokumentasikan di `API.md`.
- **Alasan:** Memisahkan concern frontend/backend secara jelas, memungkinkan React dan Laravel dikembangkan/diuji secara independen selama contract dipatuhi.
- **Referensi:** `API.md` §1–3, §8.

## 6. Design Image Sebagai Visual Implementation Reference

- **Keputusan:** Design image (screenshot final di `design/`) adalah **visual target** yang diimplementasikan seakurat mungkin; `DESIGN.md` adalah **design-system reference** (aturan token, spacing, state) untuk melengkapi apa yang tidak terlihat jelas dari gambar.
- **Alasan:** Menghindari ambiguitas ketika translasi desain ke kode — tujuan implementasi bukan mendesain ulang, melainkan mengimplementasikan desain yang sudah difinalisasi.
- **Conflict Rule (berlaku bila `DESIGN.md` berbeda dengan design image):**
  1. Design image adalah visual target — jika gambar merupakan final design, implementasikan visual berdasarkan gambar.
  2. `DESIGN.md` dipakai untuk memahami aturan yang tidak terlihat jelas dari gambar (mis. hex warna persis, perilaku hover/focus/loading).
  3. **Business logic tetap lebih tinggi** — design image tidak boleh mengubah business rules, authentication, authorization, database rules, API contract, security, user ownership, atau seller ownership. Jika ada konflik antara design dengan business/technical requirement, konflik tersebut **dilaporkan**, bukan diputuskan sepihak oleh AI.
  4. Jangan redesign: jangan mengganti layout, component hierarchy, visual style, atau UX flow yang sudah ada di gambar, kecuali diminta secara eksplisit.
- **Referensi:** `DESIGN.md` intro; `ROUTES.md` intro.

## 7. Arah Visual "Editorial Monochrome Commerce"

- **Keputusan:** Pazarz mengadopsi arah visual monokrom-editorial (tipografi besar, whitespace lega, warna aksen minimal) di ketiga surface, alih-alih gaya "flash-sale marketplace" yang umum di kategori ini.
- **Alasan:** Selaras dengan positioning produk sebagai marketplace premium/tepercaya (`PRD.md` §1.3), membedakan Pazarz dari kompetitor yang terasa murah/cluttered.
- **Referensi:** `DESIGN.md` §0–1.

## 8. Dark Mode untuk Seller/Admin Dashboard (Opsional untuk Customer)

- **Keputusan:** Dark mode direkomendasikan tersedia untuk Seller & Admin Dashboard (mengurangi kelelahan mata pada kerja operasional jangka panjang), namun opsional/tidak wajib untuk Customer-facing storefront.
- **Referensi:** `DESIGN.md` §2 (Dark Mode token).

## 9. RBAC Granular, Bukan Role-Check Biner

- **Keputusan:** Otorisasi memakai model permission granular (`role_permissions`) di atas 3 role dasar (customer/seller/admin), bukan hardcode `if role === 'admin'`.
- **Alasan:** Memungkinkan penambahan sub-role admin (mis. `admin_support`, `admin_finance`) di masa depan tanpa mengubah struktur inti otorisasi.
- **Referensi:** `ARCHITECTURE.md` §7.5, §7.8.

## 10. Snapshot Data pada Order Items untuk Integritas Historis

- **Keputusan:** `order_items` menyimpan snapshot (`product_name_snapshot`, `variant_label_snapshot`, `price_snapshot`) alih-alih hanya referensi FK ke `products`/`product_variants`.
- **Alasan:** Riwayat transaksi harus tetap akurat & immutable meskipun produk diedit/dihapus atau harga berubah setelah transaksi terjadi — ini denormalisasi yang disengaja, bukan pelanggaran normalisasi.
- **Referensi:** `DATABASE.md` §2.21, §11, §15.

---

## Asumsi yang Masih Perlu Dikonfirmasi

Item berikut diasumsikan secara wajar dari konteks dokumen sumber namun belum ditetapkan secara eksplisit oleh pemilik produk — perlu konfirmasi sebelum/selama fase development:

1. **Payment gateway spesifik** yang akan diintegrasikan pada Phase 9 (nama provider, metode yang didukung selain VA/e-wallet/card) belum ditentukan.
2. **Courier/shipping provider** spesifik untuk integrasi ongkir & tracking belum ditentukan.
3. **Window waktu** untuk auto-complete order setelah `delivered` (disebut "N hari" di `USER-FLOW.md` §2.1) dan window pengajuan dispute belum memiliki angka final.
4. **Commission rate** default per seller dan mekanisme perubahannya (per-seller custom vs global default) belum dirinci.
5. Kebijakan **partial cancel per item** dalam satu sub-order (disebut sebagai opsi di `USER-FLOW.md` §5) belum diputuskan apakah masuk MVP atau Future.
6. Pilihan konkret **session driver** (database vs redis) untuk Blade dan strategi **queue driver** untuk notifikasi/email belum ditentukan — akan diputuskan di fase development (`ARCHITECTURE.md` §3.2).
7. Cakupan pasti **dark mode Customer** (apakah benar-benar dibangun di MVP atau hanya disiapkan tokennya) belum final.
