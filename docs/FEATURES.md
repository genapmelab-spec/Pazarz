# FEATURES.md — Feature Inventory

Dokumen ini memecah `PRD.md` menjadi inventaris fitur granular per domain, dipisahkan **MVP** vs **Future**. Digunakan sebagai acuan scope saat menyusun `IMPLEMENTATION-PLAN.md` dan saat memvalidasi API/UI terhadap fitur yang benar-benar dibutuhkan.

Legenda: 🟢 MVP · 🔵 Future

---

## 1. Authentication

| Fitur | Surface | Status |
|---|---|---|
| Register (email/password) | Customer | 🟢 |
| Email verification | Customer | 🟢 |
| Login/Logout | Customer, Seller, Admin | 🟢 |
| Password reset | Customer, Seller, Admin | 🟢 |
| Session management (Blade) | Seller, Admin | 🟢 |
| Token-based auth (React) | Customer | 🟢 |
| Admin dibuat via seeding/invitation (no self-registration) | Admin | 🟢 |
| 2FA untuk Admin | Admin | 🔵 |
| Social login (Google/Apple) | Customer | 🔵 |

## 2. Customer

| Fitur | Status |
|---|---|
| Browse, search, filter, sort katalog | 🟢 |
| Product detail (gallery, varian, review) | 🟢 |
| Wishlist | 🟢 |
| Cart (multi-seller, grouped) | 🟢 |
| Checkout multi-seller (satu pembayaran) | 🟢 |
| Order tracking & riwayat | 🟢 |
| Review & rating (post-purchase) | 🟢 |
| Address book (CRUD alamat) | 🟢 |
| Profile & account settings | 🟢 |
| Notifikasi in-app | 🟢 |
| Follow toko/seller | 🔵 |
| Chat dengan seller | 🔵 |
| Rekomendasi produk berbasis behavior | 🔵 |

## 3. Seller

| Fitur | Status |
|---|---|
| Registrasi & onboarding seller | 🟢 |
| Verifikasi dokumen seller oleh Admin | 🟢 |
| Store profile setup (logo, banner, deskripsi, alamat) | 🟢 |
| Manajemen produk (create/edit/varian/gambar) | 🟢 |
| Manajemen inventory per varian | 🟢 |
| Manajemen sub-order (confirm/ship/cancel) | 🟢 |
| Input resi pengiriman | 🟢 |
| Store settings (pengiriman default, dsb.) | 🟢 |
| Balas review | 🟢 |
| Analitik penjualan dasar (revenue, chart) | 🟢 |
| Promosi & kupon toko | 🟢 |
| Analitik lanjutan (forecasting, cohort) | 🔵 |
| Multi-warehouse per toko | 🔵 |

## 4. Admin

| Fitur | Status |
|---|---|
| Dashboard overview platform (metrik GMV, order, dsb.) | 🟢 |
| Manajemen user (suspend/aktifkan) | 🟢 |
| Verifikasi & manajemen seller | 🟢 |
| Manajemen kategori & atribut global | 🟢 |
| Moderasi produk | 🟢 |
| Monitoring order & payment (read-only + override khusus) | 🟢 |
| Moderasi review & laporan (reports) | 🟢 |
| Dispute resolution & mediasi | 🟢 |
| Manajemen promosi platform-wide | 🟢 |
| Audit log | 🟢 |
| Platform settings (komisi, kebijakan dispute window) | 🟢 |
| Laporan & analitik lanjutan (export, multi-metric) | 🔵 |
| Role & permission editor custom | 🔵 |

## 5. Products & Product Variants

| Fitur | Status |
|---|---|
| Produk dengan deskripsi, harga dasar, kategori | 🟢 |
| Varian produk (kombinasi atribut, mis. warna/ukuran) dengan SKU & harga sendiri | 🟢 |
| Galeri gambar produk (multi-image, primary image) | 🟢 |
| Status produk (draft/active/inactive/archived) | 🟢 |
| Atribut produk custom per kategori | 🟢 |
| Bundling produk | 🔵 |
| Produk digital/non-fisik | 🔵 |

## 6. Categories

| Fitur | Status |
|---|---|
| Kategori hierarkis (parent-child) | 🟢 |
| Kelola kategori & atribut oleh Admin | 🟢 |
| Kategori dinamis rekomendasi AI | 🔵 |

## 7. Inventory

| Fitur | Status |
|---|---|
| Stok per varian (`inventories`) | 🟢 |
| Reserved quantity saat checkout (mencegah oversell) | 🟢 |
| Low-stock threshold & indikator | 🟢 |
| Bulk update stok | 🟢 |
| Multi-gudang/lokasi stok | 🔵 |

## 8. Cart

| Fitur | Status |
|---|---|
| Cart per user, grouped per toko | 🟢 |
| Update quantity, hapus item | 🟢 |
| Price snapshot saat item ditambahkan | 🟢 |
| Validasi stok real-time | 🟢 |
| Simpan item ke wishlist dari cart | 🟢 |

## 9. Checkout

| Fitur | Status |
|---|---|
| Pilih alamat pengiriman | 🟢 |
| Pilih metode pengiriman per toko/sub-order | 🟢 |
| Apply kupon/promo | 🟢 |
| Integrasi 1 payment gateway | 🟢 |
| Split order menjadi sub-order otomatis | 🟢 |
| Multi payment gateway pilihan customer | 🔵 |
| Cicilan/paylater | 🔵 |

## 10. Orders & Sub-Orders

| Fitur | Status |
|---|---|
| Order induk (satu payment, banyak sub-order) | 🟢 |
| Sub-order lifecycle (`pending → confirmed → processing → shipped → completed`, cabang `cancelled`/`disputed`) | 🟢 |
| Riwayat status (audit trail) | 🟢 |
| Konfirmasi diterima oleh customer | 🟢 |
| Partial cancel per item dalam sub-order | 🔵 |

## 11. Addresses

| Fitur | Status |
|---|---|
| CRUD alamat customer (polymorphic ke user/store) | 🟢 |
| Alamat default | 🟢 |
| Validasi wilayah (provinsi/kota/kecamatan) | 🟢 |
| Integrasi peta/pin lokasi | 🔵 |

## 12. Payment

| Fitur | Status |
|---|---|
| Integrasi payment gateway pihak ketiga | 🟢 |
| Webhook callback status pembayaran (idempotent) | 🟢 |
| Riwayat pembayaran | 🟢 |
| Refund parsial (per sub-order) | 🟢 |
| Multi-currency | 🔵 |

## 13. Reviews

| Fitur | Status |
|---|---|
| Review & rating (1–5) terikat ke `order_item` | 🟢 |
| Upload foto review | 🟢 |
| Balasan seller terhadap review | 🟢 |
| Moderasi review oleh Admin (hide/restore/warn) | 🟢 |
| Review terverifikasi vs tidak (badge) | 🔵 |

## 14. Wishlist

| Fitur | Status |
|---|---|
| Simpan produk favorit | 🟢 |
| Tambah ke cart dari wishlist | 🟢 |
| Wishlist sharing/public | 🔵 |

## 15. Notifications

| Fitur | Status |
|---|---|
| Notifikasi in-app (order, payment, review) | 🟢 |
| Email untuk event kritikal (verifikasi, reset password, order) | 🟢 |
| Push notification | 🔵 |
| Preferensi notifikasi granular per user | 🔵 |

## 16. Shipping

| Fitur | Status |
|---|---|
| Integrasi courier API pihak ketiga (cek ongkir, buat pengiriman) | 🟢 |
| Tracking status pengiriman (`shipment_tracking_events`) | 🟢 |
| Multi-courier pilihan seller | 🟢 |
| Self-pickup/same-day delivery | 🔵 |

## 17. Promotions

| Fitur | Status |
|---|---|
| Promosi diskon per toko (percentage/fixed) | 🟢 |
| Kupon toko & platform-wide dengan usage limit | 🟢 |
| Kupon platform-wide dibuat Admin | 🟢 |
| Bundling & tiered discount | 🔵 |
| Flash sale terjadwal dengan countdown | 🔵 |

---

## 18. Ringkasan Scope

MVP mencakup seluruh alur transaksi inti end-to-end (discovery → checkout → order lifecycle → review) beserta tooling operasional dasar untuk Seller dan Admin, sesuai `PRD.md` §14. Fitur di luar itu masuk Future Scope (`PRD.md` §15) dan tidak diimplementasikan pada fase pertama, namun skema database (`DATABASE.md`) sudah dirancang agar tidak menutup kemungkinan ekstensi ke fitur Future tersebut.
