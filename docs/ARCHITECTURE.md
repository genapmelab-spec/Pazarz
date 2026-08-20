# ARCHITECTURE.md — System Architecture

---

## 1. High-Level System Map

```text
                              PAZARZ PLATFORM
                                     │
              ┌──────────────────────┴──────────────────────┐
              │                                              │
        CUSTOMER SURFACE                              LARAVEL BACKEND
        (React + Vite SPA)                                   │
              │                                ┌──────────────┴──────────────┐
              │  REST API (JSON, token auth)   │                             │
              └────────────────────────────────▶        API Layer            │
                                                 │   (/api/v1/*, stateless)   │
                                                 └──────────────┬──────────────┘
                                                                │
                                                  ┌─────────────┴─────────────┐
                                                  │      Application Core      │
                                                  │  (Services / Actions /     │
                                                  │   Policies / Events)       │
                                                  └─────────────┬─────────────┘
                                                                │
                              ┌─────────────────────────────────┼─────────────────────────────────┐
                              │                                 │                                 │
                    BLADE WEB (session auth)                MySQL DATABASE                  External Services
                              │                                                          (Payment GW, Courier API,
              ┌───────────────┴───────────────┐                                            Email/Notification)
              │                               │
     Seller Dashboard                 Admin Dashboard
     (Laravel Blade)                  (Laravel Blade)
```

---

## 2. Backend Architecture (Laravel)

Laravel bertindak sebagai **single source of truth** untuk seluruh business logic, dikonsumsi oleh dua permukaan berbeda:
1. **API Layer** (`routes/api.php`) — dikonsumsi React (Customer).
2. **Web Layer** (`routes/web.php`) — dirender langsung sebagai Blade (Seller & Admin Dashboard).

**Prinsip kunci:** business logic (validasi, kalkulasi harga, perubahan status order, dsb) ditempatkan di **layer Service/Action**, bukan di Controller — agar logic yang sama dapat dipakai ulang oleh API Controller maupun Blade Controller tanpa duplikasi.

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/          → controller untuk React (return JSON)
│   │   └── Web/
│   │       ├── Seller/   → controller untuk Seller Dashboard (return Blade view)
│   │       └── Admin/    → controller untuk Admin Dashboard (return Blade view)
│   ├── Requests/         → Form Request validation (dipakai kedua layer)
│   └── Resources/        → API Resource (transformer response JSON)
├── Services/              → business logic reusable (mis. OrderService, CheckoutService)
├── Actions/               → single-purpose operations (mis. PlaceOrderAction)
├── Policies/               → resource ownership authorization (lihat ROLES.md)
├── Models/
├── Events/ & Listeners/    → mis. OrderPlaced → kirim notifikasi ke seller
└── Notifications/
```

## 3. Frontend Architecture (Customer — React + Vite)

- **SPA murni**, mengonsumsi Pazarz REST API (`/api/v1/*`).
- State management terpisah antara **server state** (data dari API — direkomendasikan pola query/cache seperti React Query) dan **client state** (UI state — cart drawer terbuka, filter aktif, dst).
- Struktur folder berbasis fitur (feature-based), bukan berbasis tipe file semata, agar scalable:
```text
src/
├── features/
│   ├── auth/
│   ├── catalog/
│   ├── cart/
│   ├── checkout/
│   ├── orders/
│   └── profile/
├── components/     → shared UI components (mengikuti DESIGN.md)
├── layouts/
├── lib/            → API client, utils
└── routes/
```

## 4. Blade Architecture (Seller & Admin)

- Kedua dashboard berbagi **layout dasar & komponen Blade** (sidebar, topbar, table, card) namun dengan **navigasi dan permission berbeda**.
- Direkomendasikan struktur:
```text
resources/views/
├── layouts/
│   ├── dashboard.blade.php     → shared shell (sidebar+topbar slot)
│   └── partials/
├── seller/
│   ├── dashboard.blade.php
│   ├── products/
│   ├── orders/
│   └── settings/
└── admin/
    ├── dashboard.blade.php
    ├── users/
    ├── sellers/
    ├── products/
    └── settings/
```
- Interaktivitas ringan (filter table, modal konfirmasi) dapat memakai pendekatan hybrid (mis. Alpine.js/Livewire) — keputusan tool spesifik didokumentasikan lebih lanjut pada fase development, bukan fase ini.

## 5. API Architecture

- API bersifat **stateless** (token-based), versioned (`/api/v1/`), mengikuti konvensi di `API.md`.
- API Resource class dipakai untuk membentuk response JSON yang konsisten dan menyembunyikan struktur internal database dari client.

## 6. Database Architecture

- MySQL sebagai single relational store untuk seluruh data transaksional (lihat `ERD.md` & `DATABASE.md`).
- Tidak ada database terpisah per role — seluruh permukaan (API & Blade) membaca/menulis ke database yang sama melalui Model/Service layer yang sama, menjamin konsistensi data real-time antar dashboard.

## 7. Authentication & Authorization Architecture

Lihat detail lengkap di `AUTH.md` (authentication) dan `ROLES.md` (authorization). Ringkasan: token-based untuk React, session-based untuk Blade, keduanya diverifikasi melalui satu sistem User/Role/Permission yang sama di Laravel.

## 8. Communication Between Systems

| Dari | Ke | Mekanisme |
|---|---|---|
| React (Customer) | Laravel | REST API (HTTPS, JSON) |
| Blade (Seller/Admin) | Laravel | Direct (server-rendered, same app) |
| Laravel | Payment Gateway | Outbound API call + webhook callback untuk update status pembayaran |
| Laravel | Courier/Shipping Provider | Outbound API call (cek ongkir, buat pengiriman) + webhook untuk update tracking |
| Laravel | Email/Notification Service | Queue-based job (async, tidak blocking request) |

## 9. Shared Business Logic

Contoh logic yang **wajib** ditempatkan di Service layer agar konsisten di seluruh entry point (API & Blade):
- Kalkulasi harga akhir order (subtotal, diskon, ongkir, grand total).
- Validasi & pengurangan stok saat checkout (mencegah race condition oversell — direkomendasikan pakai locking/transaction di level database saat fase development).
- Perubahan status order/sub-order beserta efek sampingnya (notifikasi, pencatatan histori).
- Perhitungan komisi platform terhadap seller saat sub-order `completed`.

## 10. Scalability & Maintainability Considerations

- Query-heavy endpoints (catalog listing, admin reports) dirancang untuk mendukung pagination & filtering dari awal (bukan tambahan belakangan).
- Event-driven side effects (notifikasi, audit log) memakai Laravel Events/Listeners agar Controller/Service tetap fokus pada logic inti (single responsibility).
- Struktur modular (Services/Actions per domain) memudahkan ekstraksi ke service terpisah di masa depan jika platform tumbuh besar (bukan kebutuhan MVP, namun arsitektur tidak menutup opsi ini).
