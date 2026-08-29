# Pazarz

**Pazarz** adalah platform e-commerce marketplace multi-vendor yang mempertemukan Customer, Seller, dan Admin dalam satu ekosistem transaksi. Lihat `docs/PRD.md` untuk detail produk lengkap.

> **Status saat ini: Documentation Phase.** Repository ini berisi dokumentasi arsitektur & spesifikasi desain — **belum ada kode implementasi**. Lihat §Development Workflow di bawah.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel |
| Backend UI (Seller & Admin) | Laravel Blade |
| Customer Frontend | React + Vite |
| Database | MySQL |
| API | Laravel REST API |

## Architecture (Ringkas)

```text
Customer
   ↓
React + Vite
   ↓
Laravel REST API
   ↓
Laravel Backend
   ├── Database
   ├── Business Logic
   ├── Admin Blade
   └── Seller Blade
```

- **Customer** wajib React + Vite (SPA, konsumsi REST API).
- **Seller Dashboard** wajib Laravel Blade.
- **Admin Dashboard** wajib Laravel Blade.
- Laravel adalah **single source of truth** untuk seluruh business logic — dikonsumsi oleh API (React) maupun langsung oleh Blade (Seller/Admin). Detail lengkap: `docs/ARCHITECTURE.md`.

## Directory Structure

```text
pazarz-documentation/
│
├── README.md                      ← file ini
│
├── docs/
│   ├── PRD.md                     ← product requirements
│   ├── FEATURES.md                ← feature inventory (MVP vs Future)
│   ├── USER-FLOW.md               ← customer/seller/admin/business flow
│   ├── ARCHITECTURE.md            ← system, auth & authorization architecture
│   ├── DATABASE.md                ← entity design & schema conventions
│   ├── API.md                     ← REST API spec (Customer-facing)
│   ├── DESIGN.md                  ← design system (tokens, components)
│   ├── ROUTES.md                  ← route list + per-page UI spec + design reference
│   ├── IMPLEMENTATION-PLAN.md     ← phased development plan
│   ├── AI-CODING-RULES.md         ← rules for AI coding agent
│   └── DECISIONS.md               ← architectural decisions & open assumptions
│
└── design/
    ├── customer/                  ← final design screenshots (React pages)
    ├── seller/                    ← final design screenshots (Seller Blade pages)
    └── admin/                     ← final design screenshots (Admin Blade pages)
```

## Documentation Map

Baca dalam urutan berikut untuk onboarding:

1. `docs/PRD.md` — kenapa Pazarz dibangun, untuk siapa, apa scope-nya.
2. `docs/FEATURES.md` — daftar fitur granular per domain, MVP vs Future.
3. `docs/USER-FLOW.md` — bagaimana tiap role menjalani flow inti (auth, purchase, dispute, dsb).
4. `docs/ARCHITECTURE.md` — bagaimana sistem disusun (backend, frontend, database, auth/authorization).
5. `docs/DATABASE.md` — skema database & konvensi migration.
6. `docs/API.md` — kontrak REST API untuk Customer.
7. `docs/DESIGN.md` — design system (warna, tipografi, spacing, komponen).
8. `docs/ROUTES.md` — route per surface + spesifikasi tiap halaman + rujukan gambar desain di `design/`.
9. `docs/IMPLEMENTATION-PLAN.md` — urutan fase development.
10. `docs/AI-CODING-RULES.md` — aturan wajib untuk siapa pun (AI atau manusia) yang menulis kode Pazarz.
11. `docs/DECISIONS.md` — keputusan arsitektur penting & asumsi terbuka yang masih perlu dikonfirmasi.

## Design Images vs DESIGN.md

- **Design image** (folder `design/`) = **visual target final** — layout aktual, spacing, component placement, typography appearance, image placement, navigation, cards, buttons, tables.
- **`docs/DESIGN.md`** = **design-system reference** — aturan desain (token warna, skala tipografi, spacing scale, component principles, responsive & accessibility rules) untuk melengkapi apa yang tidak terlihat jelas dari gambar.
- Jika keduanya berbeda: gambar desain diikuti untuk visual, `DESIGN.md` tetap dipakai untuk hal yang tidak tampak dari gambar. Business logic (auth, authorization, database rules, API contract, security, ownership) selalu lebih tinggi daripada keduanya. Detail lengkap: `docs/DECISIONS.md` §6.

## Development Workflow

Fase ini (**Phase 0 — Documentation**, lihat `docs/IMPLEMENTATION-PLAN.md`) hanya menghasilkan dokumentasi & spesifikasi desain. **Belum ada migration, model, controller, komponen React, komponen Blade, atau implementasi API yang ditulis.** Implementasi dimulai pada Phase 1 sesuai urutan fase di `docs/IMPLEMENTATION-PLAN.md`, dengan aturan wajib di `docs/AI-CODING-RULES.md` diikuti sepanjang development.

## Frontend / Backend Boundaries

| Surface | Teknologi | Tidak boleh dipakai untuk |
|---|---|---|
| Customer | React + Vite | Seller, Admin |
| Seller Dashboard | Laravel Blade | Customer publik |
| Admin Dashboard | Laravel Blade | Customer publik |

Pembagian ini bersifat final dan tidak boleh diubah tanpa instruksi eksplisit — lihat `docs/AI-CODING-RULES.md` #2–3 dan `docs/DECISIONS.md` §1.
