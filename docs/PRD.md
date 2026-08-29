# PRD.md — Product Requirements Document

**Product:** Pazarz — E-Commerce Marketplace Multi-Vendor
**Status:** Documentation Phase (Pre-Development)
**Owner:** Product Team

---

## 1. Product Overview

### 1.1 Apa itu Pazarz

Pazarz adalah platform marketplace multi-vendor yang mempertemukan **Customer**, **Seller**, dan **Admin** dalam satu ekosistem transaksi yang aman, cepat, dan dapat dipercaya. Pazarz bukan sekadar katalog produk — Pazarz adalah infrastruktur perdagangan digital yang menangani seluruh siklus hidup transaksi: dari penemuan produk, pemilihan varian, checkout, pembayaran, pengiriman, hingga purna-jual (review, dispute, retur).

### 1.2 Value Proposition

- **Customer:** Pengalaman belanja cepat, personal, dan tepercaya — discovery relevan, checkout minim friksi, kepastian status pesanan real-time.
- **Seller:** Dashboard operasional efisien untuk mengelola toko, produk, stok, pesanan, dan promosi tanpa kebutuhan teknis, dengan visibilitas penuh atas performa penjualan.
- **Admin:** Kontrol penuh atas kesehatan platform — moderasi, kualitas seller, penyelesaian sengketa, dan insight bisnis — dengan tooling yang powerful namun tidak membebani secara operasional.

### 1.3 Product Vision

Menjadi marketplace generasi baru yang terasa **premium, tepercaya, dan modern** — bukan marketplace "diskon murah" generik — dengan pengalaman produk sekelas platform fashion/lifestyle premium (lihat arah visual di `DESIGN.md`), namun dengan kedalaman fitur marketplace multi-vendor penuh.

---

## 2. Problem Statement

1. Customer kesulitan menemukan produk yang relevan & tepercaya di tengah katalog besar dan kualitas seller yang bervariasi.
2. Seller kecil-menengah butuh tools operasional (inventory, order management, analitik) yang sering hanya tersedia di platform besar dengan kompleksitas tinggi.
3. Admin platform butuh visibilitas dan kontrol terpusat untuk menjaga kualitas marketplace (produk, seller, transaksi) tanpa proses manual yang lambat.
4. Marketplace generik seringkali punya UX yang terasa murah/cluttered, menurunkan kepercayaan pengguna terhadap platform maupun seller di dalamnya.

## 3. Vision & Goals

- Membangun marketplace multi-vendor yang scalable (bukan CRUD sederhana) dengan pemisahan tanggung jawab yang jelas antar role, sesuai arsitektur hybrid React (Customer) + Laravel Blade (Seller/Admin) — lihat `ARCHITECTURE.md`.
- Menyediakan pengalaman checkout dan order lifecycle yang transparan dan reliable, termasuk untuk order multi-seller (split order per seller/`sub_orders`).
- Memberikan seller tools manajemen toko yang lengkap: produk, varian, stok, pesanan, promosi, analitik pendapatan.
- Memberikan admin kontrol penuh: manajemen user/seller, kategori, moderasi produk & review, penyelesaian dispute, dan laporan platform.
- Membangun fondasi desain (design system) yang konsisten dan reusable di tiga permukaan (Customer/Seller/Admin) — lihat `DESIGN.md`.

## 4. Non-Goals

- Tidak membangun fitur social-commerce (live shopping, feed sosial) pada fase ini.
- Tidak membangun sistem logistik sendiri — Pazarz terhubung ke penyedia jasa kirim eksternal, tidak membangun armada sendiri.
- Tidak membangun payment gateway sendiri — Pazarz terintegrasi ke payment gateway pihak ketiga, bukan memproses kartu secara langsung.
- Tidak membangun aplikasi native mobile pada fase pertama (fokus web responsive dahulu).
- Tidak menulis kode implementasi pada fase dokumentasi ini (lihat catatan di `README.md`).

## 5. Target Users

| Role | Deskripsi | Surface |
|---|---|---|
| **Customer** | Pengguna akhir yang mencari dan membeli produk. | React + Vite |
| **Seller** | Individu/bisnis yang membuka toko dan menjual produk di Pazarz. | Laravel Blade |
| **Admin** | Tim internal Pazarz yang mengelola dan menjaga kualitas platform. | Laravel Blade |

## 6. User Personas

**"Dinda" — Customer, 27 th, Marketing Executive**
Sibuk, terbiasa belanja online, sensitif terhadap kepercayaan (review, keaslian produk) dan kecepatan checkout. Mudah churn jika UX terasa lambat/berantakan.

**"Bima" — Seller, 34 th, Pemilik brand streetwear kecil**
Mengelola toko sendirian, butuh dashboard yang cepat dipahami untuk kelola stok & pesanan tanpa staf IT. Sangat peduli terhadap data penjualan real-time.

**"Admin Sari" — Platform Operations, 30 th**
Bertanggung jawab menjaga kualitas katalog & menyelesaikan komplain. Butuh tools bulk-action dan laporan cepat, bekerja dengan volume data besar setiap hari.

## 7. Core Features (Ringkasan per Role)

Detail lengkap & pemisahan MVP/Future ada di `FEATURES.md`.

- **Customer:** Auth, profile & address book, discovery (browse/search/filter/kategori), product detail, wishlist, cart, checkout multi-seller, pembayaran, order tracking, review & rating, notifikasi, pengaturan akun.
- **Seller:** Onboarding & verifikasi toko, store profile, manajemen produk (termasuk varian & gambar), manajemen inventory, manajemen pesanan (per sub-order), pengiriman, promosi & kupon, balasan review, analitik penjualan & revenue, notifikasi, pengaturan toko.
- **Admin:** Dashboard overview platform, manajemen user, manajemen & verifikasi seller, manajemen kategori & atribut, moderasi produk, manajemen pesanan & pembayaran (monitoring), moderasi review, manajemen promosi platform, penyelesaian dispute, laporan & analitik, audit log, pengaturan platform.

## 8. Marketplace Model

- **Model:** Multi-vendor marketplace — satu customer dapat berbelanja dari banyak seller dalam satu transaksi checkout.
- **Order splitting:** Satu `order` (induk, satu pembayaran) dipecah menjadi beberapa `sub_orders` (satu per seller yang terlibat), masing-masing memiliki status, ongkir, dan lifecycle pemrosesan independen. Lihat `DATABASE.md` §Entity Detail dan `USER-FLOW.md` §Multi-Vendor Order Flow.
- **Komisi:** Platform mengambil komisi per seller (`sellers.commission_rate`) dihitung saat sub-order berstatus `completed` — lihat `ARCHITECTURE.md` §Shared Business Logic.

## 9. Payment Strategy

- Pazarz **tidak** membangun payment processor sendiri — terintegrasi ke payment gateway pihak ketiga (VA, e-wallet, kartu) via outbound API call + webhook callback.
- Satu `payment` merepresentasikan satu `order` (bukan per sub-order) — grand total dibayar sekaligus oleh customer, lalu didistribusikan secara internal ke masing-masing seller berdasarkan sub-order.
- Endpoint webhook (`/checkout/payment-callback`) wajib idempotent — lihat `API.md` §7.9.
- Refund untuk sub-order yang dibatalkan/dispute bersifat parsial terhadap payment induk, tidak membatalkan seluruh order.

## 10. Business Rules Kunci

- Order induk **tidak otomatis batal** jika salah satu sub-order dibatalkan (mis. stok habis di satu seller) — hanya sub-order tersebut yang membatalkan & memicu refund parsial.
- Historical order harus tetap aman: `order_items` menyimpan **snapshot** (nama produk, nama varian, SKU, harga) agar riwayat transaksi tidak berubah walau master data produk berubah kemudian — lihat `DATABASE.md`.
- Seller hanya dapat mengelola resource miliknya sendiri (product, sub-order, store settings); Admin memiliki override lintas kepemilikan untuk keperluan moderasi/support — lihat `ARCHITECTURE.md` §Authorization.
- Setiap aksi admin sensitif (suspend user, reject seller, hapus produk, keputusan dispute) wajib tercatat di `audit_logs`.
- Review hanya dapat dibuat untuk `order_item` yang statusnya sudah `completed`, satu review per `order_item`.

## 11. Success Criteria

- **Customer:** completion rate checkout tinggi, response API baca < 300ms pada beban normal, tidak ada laporan oversell (stok tidak konsisten).
- **Seller:** waktu onboarding-ke-toko-live singkat, dashboard dapat memproses order tanpa keterlambatan notifikasi.
- **Admin:** waktu rata-rata verifikasi seller & moderasi produk/review menurun berkat queue terpusat; seluruh aksi sensitif tercatat di audit log tanpa celah.
- **Platform:** arsitektur backend stateless-friendly (siap horizontal scale), skema database mendukung pertumbuhan katalog & transaksi tanpa redesign besar.

## 12. Functional Requirements (Ringkasan)

- Sistem harus mendukung registrasi & login terpisah secara UX untuk Customer (React) vs Seller/Admin (Laravel Blade), namun berbagi model otorisasi yang sama di backend.
- Sistem harus mendukung produk dengan varian (misal: ukuran, warna) yang masing-masing memiliki SKU & stok sendiri.
- Sistem harus memecah satu order customer menjadi beberapa `sub_order` berdasarkan seller.
- Sistem harus mencatat setiap perubahan status penting (order, payment, dispute) sebagai riwayat status yang dapat diaudit.
- Sistem harus mendukung soft-delete pada entity yang berdampak ke riwayat transaksi (produk, toko, user).
- Sistem harus mendukung role & permission granular (lihat `ARCHITECTURE.md` §Authorization), bukan hanya role-check biner.
- Sistem harus mengirim notifikasi (in-app minimal, email untuk event kritikal) untuk event penting di tiap role.

## 13. Non-Functional Requirements

- **Security:** Password hashing standar industri, rate-limiting endpoint auth, validasi ownership resource di setiap request, audit log untuk aksi admin sensitif.
- **Performance:** Query katalog & search mendukung pagination & indexing yang tepat; response API < 300ms untuk operasi baca umum pada beban normal.
- **Scalability:** Skema database dirancang untuk pertumbuhan katalog & transaksi (index tepat, normalisasi wajar).
- **Availability:** Arsitektur backend stateless-friendly agar dapat di-scale horizontal di kemudian hari.
- **Maintainability:** Pemisahan concern jelas antar layer, penamaan konsisten.

## 14. MVP Scope

- Auth (Customer/Seller/Admin) + verifikasi email.
- Discovery: browse, search, filter kategori/harga, product detail.
- Cart, wishlist, checkout multi-seller, integrasi 1 payment gateway.
- Order lifecycle penuh (checkout → completed) + sub-order per seller.
- Seller: onboarding, manajemen produk & varian, inventory, order processing, store settings dasar.
- Admin: manajemen user/seller/produk/kategori, monitoring order & payment, moderasi review dasar.
- Review & rating produk (post-purchase).
- Notifikasi in-app dasar untuk order & payment.

## 15. Future Scope

- Promosi & kupon lanjutan (bundling, flash sale, tiered discount).
- Seller analytics lanjutan (forecasting, cohort).
- Dispute & resolution center penuh dengan mediasi berjenjang.
- Multi-currency / multi-warehouse.
- Rekomendasi produk berbasis behavior.
- Seller follower & konten toko (mendekati social-commerce ringan).

## 16. Representative User Stories

```text
As a customer, I want to filter products by category, price range, and rating,
so that I can quickly narrow down relevant products.

As a customer, I want to checkout items from multiple sellers in one transaction,
so that I don't need to pay separately per seller.

As a customer, I want to track my order status in real time,
so that I know when my package will arrive.

As a seller, I want to manage stock per product variant,
so that I don't oversell an out-of-stock size/color.

As a seller, I want to see revenue analytics by period,
so that I can evaluate my store's performance.

As a seller, I want to be notified when a new order arrives,
so that I can process it quickly.

As an admin, I want to review and approve new seller registrations,
so that only legitimate sellers can sell on the platform.

As an admin, I want to see flagged products/reviews in one queue,
so that I can moderate content efficiently.

As an admin, I want to view platform-wide sales and dispute reports,
so that I can make informed operational decisions.
```
