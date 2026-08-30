# IMPLEMENTATION-PLAN.md — Implementation Phases

Dokumen ini adalah rencana fase implementasi untuk fase development (setelah fase dokumentasi ini selesai). **Tidak ada kode ditulis pada fase dokumentasi** — lihat `README.md` §Development Workflow.

---

## Phase 0 — Documentation

- **Objective:** Menyusun seluruh dokumentasi arsitektur, database, API, design system, dan UI spec sebagai acuan tunggal sebelum coding dimulai.
- **Tasks:** Menulis `PRD.md`, `FEATURES.md`, `USER-FLOW.md`, `ARCHITECTURE.md`, `DATABASE.md`, `API.md`, `DESIGN.md`, `ROUTES.md`, `DECISIONS.md`; mengumpulkan & mengorganisir design image ke `design/`.
- **Dependencies:** Tidak ada (fase pertama).
- **Definition of Done:** Seluruh dokumen di `docs/` konsisten satu sama lain (lihat `README.md` §Final Validation), design image tersedia dan ter-mapping ke `ROUTES.md`, tidak ada requirement kontradiktif antar dokumen.

## Phase 1 — Project Foundation

- **Objective:** Menyiapkan skeleton project Laravel dan React sesuai `ARCHITECTURE.md`.
- **Tasks:** Inisialisasi project Laravel (routing api/web terpisah), inisialisasi project React + Vite, setup konvensi folder (`app/Services`, `app/Actions`, `src/features`, dst.), setup environment/config dasar, setup design token dasar (Tailwind config/CSS variables) sesuai `DESIGN.md` §2–5.
- **Dependencies:** Phase 0.
- **Definition of Done:** Kedua project dapat dijalankan lokal, struktur folder sesuai `ARCHITECTURE.md` §2–4, design token terpasang dan dapat direferensikan komponen dasar.

## Phase 2 — Authentication

- **Objective:** Mengimplementasikan authentication untuk ketiga role sesuai `ARCHITECTURE.md` §7.
- **Tasks:** Migration `users`, `roles`, `permissions`, `role_permissions`; implementasi register/login/logout/email verification untuk Customer (token-based); implementasi login/session untuk Seller & Admin (Blade); implementasi password reset lintas role; setup Policy/Gate dasar.
- **Dependencies:** Phase 1.
- **Definition of Done:** Ketiga role dapat register/login sesuai flow di `USER-FLOW.md` §1, permission matrix (`ARCHITECTURE.md` §7.7) terpasang sebagai seed data.

## Phase 3 — Database

- **Objective:** Mengimplementasikan seluruh skema database sesuai `DATABASE.md`.
- **Tasks:** Menulis migration mengikuti urutan di `DATABASE.md` §14, membuat model + relationship, membuat seeder untuk data master (roles, permissions, categories, product_attributes).
- **Dependencies:** Phase 2 (tabel `users`/`roles` sudah ada).
- **Definition of Done:** Seluruh 36 entity di `DATABASE.md` §1 termigrasi dengan FK, index, dan constraint sesuai spesifikasi; seeder berjalan tanpa error.

## Phase 4 — Seller Blade

- **Objective:** Membangun Seller Dashboard sesuai `ROUTES.md` §B dan `DESIGN.md` §8.
- **Tasks:** Layout dasar Blade (sidebar/topbar), halaman Dashboard/Products/Create-Edit Product/Inventory/Orders/Order Detail/Store Settings, implementasi Service layer terkait (ProductService, InventoryService).
- **Dependencies:** Phase 3.
- **Definition of Done:** Seluruh halaman B1–B10 di `ROUTES.md` berfungsi sesuai spesifikasi (states, CTA, data), visual sesuai design image di `docs/stitch_pazarz_ui_design/backend_ui/seller/`.

## Phase 5 — Admin Blade

- **Objective:** Membangun Admin Dashboard sesuai `ROUTES.md` §C dan `DESIGN.md` §9.
- **Tasks:** Layout dasar Blade (shared dengan Seller layout shell), halaman Dashboard/Users/Sellers/Categories/Products Moderation/Orders Monitoring/Disputes/Audit Logs/Platform Settings.
- **Dependencies:** Phase 3 (dapat paralel dengan Phase 4).
- **Definition of Done:** Seluruh halaman C1–C15 di `ROUTES.md` berfungsi sesuai spesifikasi, aksi sensitif tercatat di `audit_logs`, visual sesuai design image di `docs/stitch_pazarz_ui_design/backend_ui/admin/`.

## Phase 6 — Customer React

- **Objective:** Membangun Customer frontend sesuai `ROUTES.md` §A dan `DESIGN.md` §7.
- **Tasks:** Setup routing React, layout Header/Footer, halaman Landing/Browse/Search/Product Detail/Store Detail/Profile, integrasi API client (`API.md` §1–5).
- **Dependencies:** Phase 3, endpoint API produk/kategori dari Phase 3–4 (Products & Categories API).
- **Definition of Done:** Halaman A1–A5, A11–A13 di `ROUTES.md` berfungsi dengan data real dari API, visual sesuai design image di `docs/stitch_pazarz_ui_design/frontend_ui/`.

## Phase 7 — Cart

- **Objective:** Implementasi cart end-to-end.
- **Tasks:** Endpoint `cart` (`API.md` §7.6–7.7), halaman Cart React (`ROUTES.md` A6), validasi stok real-time.
- **Dependencies:** Phase 6.
- **Definition of Done:** Customer dapat menambah/mengubah/menghapus item cart, grouped per toko, dengan validasi stok sesuai `DATABASE.md` §2.14–2.16.

## Phase 8 — Checkout & Orders

- **Objective:** Implementasi checkout multi-vendor dan order lifecycle penuh.
- **Tasks:** `CheckoutService`/`PlaceOrderAction` (split order→sub_orders, snapshot harga), endpoint checkout & payment callback (`API.md` §7.8–7.9), halaman Checkout/Payment Status/Orders/Order Detail React (`ROUTES.md` A7–A10), halaman Orders Seller (`ROUTES.md` B5–B6) dan Orders Monitoring Admin (`ROUTES.md` C7).
- **Dependencies:** Phase 7, Phase 4 (Seller order handling), Phase 5 (Admin monitoring).
- **Definition of Done:** Flow checkout end-to-end sesuai `USER-FLOW.md` §2–3 berjalan, state diagram order (`USER-FLOW.md` §2.1) terimplementasi penuh termasuk cabang `cancelled`/`disputed`.

## Phase 9 — Payment

- **Objective:** Integrasi payment gateway pihak ketiga.
- **Tasks:** Integrasi outbound API ke payment gateway, implementasi webhook idempotent, refund parsial per sub-order, migration & UI status pembayaran.
- **Dependencies:** Phase 8.
- **Definition of Done:** Pembayaran sukses/gagal/pending tercermin akurat di `orders.status`/`payments.status`, webhook duplikat tidak memproses pembayaran dua kali (lihat `API.md` §8).

## Phase 10 — Testing

- **Objective:** Memastikan reliabilitas fitur inti sebelum rilis MVP.
- **Tasks:** Test untuk business logic kritikal (kalkulasi harga, split sub-order, reservasi stok, webhook idempotency, ownership/authorization policy), test UI states (loading/empty/error) untuk halaman utama di `ROUTES.md`.
- **Dependencies:** Phase 4–9.
- **Definition of Done:** Business logic kritikal di `ARCHITECTURE.md` §9 tertutup test; tidak ada regresi pada flow checkout/order/auth.

## Phase 11 — Future Features

- **Objective:** Placeholder untuk fitur di luar MVP.
- **Tasks:** Lihat daftar lengkap Future Scope di `FEATURES.md` (🔵) — mis. promosi lanjutan, dispute mediation berjenjang, multi-currency, rekomendasi produk, seller follower/konten toko.
- **Dependencies:** MVP (Phase 0–10) rilis dan stabil.
- **Definition of Done:** Tidak berlaku pada fase ini — fase ini hanya placeholder terencana, tidak dikerjakan sebelum ada instruksi eksplisit.
