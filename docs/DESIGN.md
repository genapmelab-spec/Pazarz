# DESIGN.md — Pazarz Design System Specification

Dokumen ini menjelaskan **design rules** (design system, brand direction, typography, color, spacing, component principles). Untuk **actual visual target** (layout, hierarchy, component placement per halaman), lihat folder `design/` (screenshot) dan `ROUTES.md` (spesifikasi per halaman). Jika `DESIGN.md` berbeda dengan gambar desain di `design/`, gambar desain adalah visual target final — lihat `DECISIONS.md` §Conflict Rule.

---

## 0. Reference Analysis — Arah "Editorial Monochrome Commerce"

Analisis visual language yang menjadi dasar arah desain Pazarz:

1. **Design language:** Editorial, minimal, high-contrast monochrome + foto sebagai satu-satunya sumber warna. Terasa premium bukan karena warna ramai, tapi karena tipografi besar & whitespace lega.
2. **Typography:** Sans-serif grotesk tegas (Inter), headline besar all-caps dengan tracking rapat, line-height ketat pada display text.
3. **Color direction:** Palet nyaris monokrom — hitam pekat, putih bersih, abu netral untuk secondary text — warna hanya muncul dari foto produk/model.
4. **Spacing:** Sangat lega di section besar (hero), namun rapat & efisien pada card grid — kontras spacing dipakai untuk membedakan hierarki "hero" vs "utility content".
5. **Layout:** Grid asimetris pada storefront — hero besar, panel konten sekunder dengan card vertikal.
6. **Component pattern:** Card dengan **rounded corners besar** (radius ~16–24px), overlay teks di pojok bawah gambar dengan sedikit gradient/scrim, badge lingkaran kecil sebagai affordance interaktif.
7. **Navigation pattern:** Nav minimal — logo teks kiri, 2–3 link teks kecil kanan, satu icon-button bulat (profil/menu).
8. **Dashboard pattern:** Pola dashboard (Seller/Admin) diturunkan dari prinsip yang sama (clean, monokrom, whitespace lega) namun diadaptasi ke kebutuhan data-density lebih tinggi — dijelaskan di §7–8.
9. **Responsive behavior:** Struktur 2-kolom pada desktop collapse menjadi stack vertikal di mobile — pola default Pazarz.

**Keputusan:** Pazarz mengadopsi arah **"Editorial Monochrome Commerce"** — prinsip yang sama diterapkan lintas 3 permukaan (Customer/Seller/Admin), dengan aksen warna brand yang lebih hidup dipakai secara sangat selektif (CTA utama, status indicator) agar tetap fungsional untuk marketplace (bukan portfolio fashion semata). Lihat `DECISIONS.md`.

---

## 1. Design Philosophy

**Brand personality:** Confident, refined, quietly premium — seperti curator, bukan pasar teriak diskon.

**Visual identity:** Monokrom sebagai basis, tipografi besar sebagai "suara" utama brand (bukan ilustrasi/icon berlebihan), foto produk berkualitas tinggi sebagai satu-satunya elemen warna dominan.

**Design principles:**
1. **Typography-led, not decoration-led** — hierarki dibangun dari skala huruf, bukan warna/border berlebihan.
2. **Whitespace is a feature** — ruang kosong dipakai untuk menandakan kualitas/kepercayaan.
3. **Restraint in color** — warna aksen dipakai sesedikit mungkin agar bermakna saat dipakai (CTA, status).
4. **Consistency over novelty** — komponen yang sama berperilaku identik di ketiga surface.

**UX principles:** Progressive disclosure (jangan tampilkan semua sekaligus), predictable navigation, feedback instan untuk setiap aksi (loading/success/error selalu ada state-nya — lihat §12).

**Emotional direction:** Tenang, percaya diri, "sudah beres" — bukan urgent/FOMO-driven seperti marketplace diskon generik.

**Visual hierarchy:** Display/H1 untuk momen "hero" per halaman → satu per halaman. Card & grid untuk discovery. Table/list untuk data-dense area (dashboard).

**Hindari:** gradient neon, drop-shadow tebal ala neumorphism, warna-warni ceria generic SaaS, badge diskon besar-besar bergaya "flash sale", font playful/rounded, ikon 3D/gaming — sesuai visual direction di `README.md`/`PRD.md` (Pazarz tidak ingin terlihat seperti flash-sale/discount/crypto/gaming marketplace).

---

## 2. Color System

### Light Mode (Default)

| Token | Hex | Usage | Do NOT use for |
|---|---|---|---|
| `color-primary` | `#111111` | CTA utama, teks display, elemen fokus brand | Body text panjang (terlalu berat) |
| `color-primary-inverse` | `#FFFFFF` | Teks di atas `color-primary` | Background utama (pakai `color-background`) |
| `color-secondary` | `#4B4B4B` | Sub-heading, ikon sekunder | CTA utama |
| `color-accent` | `#2F6FED` | Link aktif, focus ring, elemen interaktif kecil (bukan CTA besar) | Body text, background luas |
| `color-background` | `#FFFFFF` | Background halaman utama | Card yang butuh elevasi |
| `color-surface` | `#F7F7F5` | Background section sekunder, sidebar | Teks |
| `color-surface-elevated` | `#FFFFFF` (+ shadow `elevation-1`) | Card, modal, dropdown | Background halaman penuh |
| `color-text-primary` | `#111111` | Heading, body utama | Disabled text |
| `color-text-secondary` | `#5E5E5E` | Deskripsi, caption, meta info | CTA text |
| `color-text-muted` | `#9A9A9A` | Placeholder, disabled label | Heading |
| `color-border` | `#E4E4E1` | Border input, card outline | Divider tebal |
| `color-divider` | `#EDEDEA` | Garis pemisah antar section | Border interaktif |
| `color-success` | `#1E8E5A` | Status sukses (paid, delivered) | Error state |
| `color-warning` | `#B98900` | Status pending/menunggu | Error state |
| `color-error` | `#D8362B` | Validasi gagal, status cancelled/dispute | Success state |
| `color-info` | `#2F6FED` | Info banner, tooltip | Error state |

### Dark Mode (Direkomendasikan untuk Seller/Admin Dashboard — opsional untuk Customer)

| Token | Hex |
|---|---|
| `color-background` (dark) | `#0E0E0E` |
| `color-surface` (dark) | `#171717` |
| `color-surface-elevated` (dark) | `#1F1F1F` |
| `color-text-primary` (dark) | `#F5F5F5` |
| `color-text-secondary` (dark) | `#B0B0B0` |
| `color-border` (dark) | `#2A2A2A` |

> Dark mode memakai peningkatan brightness `surface` untuk elevasi, bukan shadow (shadow tidak terlihat jelas di background gelap). Digunakan terutama pada Seller/Admin untuk mengurangi kelelahan mata saat kerja operasional jangka panjang.

---

## 3. Typography

**Font family:** `Inter` (fallback: `-apple-system, "Helvetica Neue", Arial, sans-serif`) — grotesk modern, netral, tegas, sangat legible di semua ukuran.

| Style | Size (desktop) | Size (mobile) | Weight | Line-height | Letter-spacing | Usage |
|---|---|---|---|---|---|---|
| Display | 64px | 40px | 700 | 1.05 | -0.02em | Hero headline (1 per halaman) |
| H1 | 40px | 28px | 700 | 1.15 | -0.01em | Judul halaman utama |
| H2 | 32px | 24px | 600 | 1.2 | -0.01em | Section title |
| H3 | 24px | 20px | 600 | 1.25 | 0 | Sub-section, card group title |
| H4 | 18px | 16px | 600 | 1.3 | 0 | Card title, table section header |
| Body Large | 18px | 16px | 400 | 1.5 | 0 | Deskripsi produk, lead paragraph |
| Body | 16px | 14px | 400 | 1.5 | 0 | Teks umum, form label value |
| Body Small | 14px | 13px | 400 | 1.45 | 0 | Meta info, helper text |
| Label | 13px | 13px | 600 | 1.3 | 0.02em (uppercase opsional) | Form label, nav item, badge |
| Caption | 12px | 12px | 400 | 1.35 | 0 | Timestamp, footnote |

**Aturan penggunaan:** Maksimal 1 elemen Display per halaman. Heading tidak boleh loncat level (H1 → H3 tanpa H2) di struktur dokumen/section yang sama.

---

## 4. Spacing System

Skala 4px-based (`--space-*`):

```text
--space-1: 4px    --space-6: 32px   --space-11: 96px
--space-2: 8px    --space-7: 40px   --space-12: 120px
--space-3: 12px   --space-8: 48px
--space-4: 16px   --space-9: 64px
--space-5: 20px   --space-10: 80px
```

**Panduan pemakaian:**
- `4–8px`: jarak antar ikon & label, internal padding badge/chip.
- `12–16px`: padding internal komponen kecil (button, input), gap antar elemen dalam card.
- `20–24px`: padding card, gap antar card dalam grid rapat.
- `32–48px`: jarak antar komponen dalam satu section.
- `64–96px`: jarak antar section pada landing/dashboard.
- `120px`: jarak vertikal hero section pada desktop besar.

---

## 5. Grid & Layout

| Surface | Container Max-Width | Columns (desktop) | Gutter | Page Padding (desktop) | Page Padding (mobile) |
|---|---|---|---|---|---|
| Customer Frontend | 1280px | 12 | 24px | 64px | 20px |
| Seller Dashboard | Fluid (100%, sidebar fixed) | 12 | 20px | 32px | 16px |
| Admin Dashboard | Fluid (100%, sidebar fixed) | 12 | 20px | 32px | 16px |

**Customer Layout:** Hero section full-bleed dimungkinkan (gambar edge-to-edge), konten di bawahnya mengikuti container max-width. Grid produk: 4 kolom desktop / 3 tablet / 2 mobile.

**Seller Dashboard Layout:** Sidebar tetap 260px (collapsible ke 72px icon-only), topbar 64px height, area konten scroll independen.

**Admin Dashboard Layout:** Sama seperti Seller, namun dengan tambahan secondary filter bar (48px) di atas data table pada halaman manajemen data besar.

---

## 6. Component System

Untuk setiap komponen: purpose, variants, sizes, states, behavior singkat. Spesifikasi per halaman (komponen mana dipakai di mana) ada di `ROUTES.md`.

### 6.1 Foundation

**Button**
- Variants: `primary` (fill hitam `color-primary`, teks putih, radius 999px/pill), `secondary` (outline 1px `color-border`, teks `color-text-primary`), `ghost` (tanpa border, teks saja), `destructive` (fill `color-error`).
- Sizes: `sm` (36px height), `md` (44px height), `lg` (52px height).
- States: default, hover (darken 8%), active (darken 12% + `scale(0.98)`), focus (ring 2px `color-accent` offset 2px), disabled (opacity 40%, no pointer), loading (spinner inline, label tetap terlihat/opacity 70%).

**Input / Select / Checkbox / Radio / Toggle**
- Height default 44px (md), border 1px `color-border`, radius 10px.
- Focus: border → `color-accent`, ring 2px opacity 20%.
- Error state: border → `color-error`, helper text merah di bawah field.
- Checkbox/Radio: 20px, radius 6px (checkbox) / full (radio), checked fill `color-primary`.
- Toggle: track 40x22px, thumb 18px, on-state fill `color-primary`.

**Badge**
- Sizes: sm (20px height), md (24px height). Radius 999px (pill) untuk status umum, radius 6px untuk tag kategori.
- Variants warna mengikuti semantic color (success/warning/error/info/neutral).

**Avatar**
- Sizes: 24 / 32 / 40 / 64px. Radius full (circle) untuk user, radius 12px untuk logo toko (square-ish, membedakan identitas personal vs brand).

**Icon**
- Grid dasar 24x24px (stroke icon, 1.5–2px stroke width, gaya line-icon minimal — bukan filled/3D).

### 6.2 Navigation

**Header (Customer)** — height 72px desktop / 60px mobile. Logo kiri (teks wordmark), nav link tengah/kanan (Label style, 3–5 item), icon-button bulat kanan (cart, profile).

**Sidebar (Seller/Admin)** — width 260px, item nav height 44px, radius 8px on hover/active (background `color-surface`), ikon 20px + label. Collapsible ke icon-only 72px.

**Breadcrumb** — Body Small, separator "/", item terakhir `color-text-primary` bold, lainnya `color-text-secondary`.

**Tabs** — underline style (border-bottom 2px `color-primary` pada tab aktif), height 44px.

**Pagination** — angka + prev/next, item aktif fill `color-primary` radius full 32px.

### 6.3 Commerce

**Product Card** — radius 16px, image ratio 3:4 atau 1:1, padding konten 12px, judul H4 truncate 2 baris, harga Body bold, rating kecil di bawah harga. Hover: scale image subtle 1.02 + shadow `elevation-1`.

**Product Gallery** — thumbnail vertikal (desktop) / horizontal scroll (mobile), main image radius 16px.

**Price** — harga aktif Body Large bold `color-text-primary`; harga coret (jika promo) Body Small `color-text-muted` strikethrough, di kiri harga aktif.

**Rating** — icon star 16px + angka Body Small + "(count)" `color-text-muted`.

**Seller Card** — avatar toko 40px + nama + rating ringkas + tombol "Follow" (secondary button sm).

**Cart Item / Order Card** — layout horizontal: image 64px radius 8px, info tengah, harga/quantity kanan.

**Checkout Summary** — surface elevated card, radius 16px, breakdown baris (subtotal/ongkir/diskon/total) dengan divider antara subtotal breakdown dan grand total (grand total di-bold, size Body Large).

### 6.4 Feedback

**Alert / Toast** — radius 12px, ikon status kiri, auto-dismiss 4s (toast), posisi top-right (desktop) / top (mobile).

**Modal** — radius 20px, max-width 480px (form) / 640px (content lebih kaya), overlay scrim `rgba(0,0,0,0.4)`.

**Drawer** — dipakai untuk Cart preview & Filter (mobile) — slide dari kanan (cart) / bawah (filter mobile), radius 20px pada sisi yang terlihat.

**Tooltip** — Body Small, background `color-primary`, teks putih, radius 6px, muncul on-hover 200ms delay.

**Skeleton** — shimmer bar radius mengikuti komponen aslinya (card skeleton = radius 16px).

**Empty State** — ilustrasi/icon sederhana line-style + Body text + CTA opsional, center-aligned, padding vertikal 64px.

**Error State** — sama seperti empty state, ikon warna `color-error`, tombol retry.

### 6.5 Dashboard

**Metric Card** — radius 16px, label Label kecil di atas, angka besar (H2/H3), trend indicator kecil (panah + persentase, warna success/error).

**Data Table** — header row background `color-surface`, row height 52px, hover row background `color-surface`, radius 12px pada container table, border antar baris `color-divider` (bukan border tebal per sel).

**Filter Bar** — kumpulan Select/Input compact (sm size) sejajar horizontal, tombol "Reset" ghost di kanan.

**Search** — input dengan icon kaca pembesar kiri, radius sesuai konteks (pill 999px di Customer header, radius 10px standard di dashboard).

**Chart Container** — surface elevated, radius 16px, padding 24px, legend di atas/kanan chart.

**Status Badge** — lihat Badge, dipetakan warna semantic sesuai status (`paid`=info, `completed`=success, `cancelled`=error, `processing`=warning).

**Activity Feed** — list item dengan timestamp Caption kiri/kanan, ikon event kecil, divider tipis antar item.

**Accessibility catatan lintas komponen:** seluruh komponen interaktif memiliki visible focus state (§6.1), target sentuh minimum 44x44px pada mobile, kontras teks minimum sesuai §11.

---

## 7. Customer UI Design Direction

- **Landing:** Hero full-bleed (foto lifestyle besar), headline Display besar, CTA pill primary. Diikuti strip kategori unggulan, section "Today's trend"-style: card grid dengan overlay teks bawah, badge panah bulat di pojok.
- **Product Discovery/Search/Category:** Grid produk 4 kolom, filter sidebar kiri (desktop) / drawer bottom (mobile), sort dropdown kanan atas grid.
- **Product Detail:** Gallery kiri, info & CTA kanan (sticky saat scroll di desktop), varian sebagai chip/swatch, tab deskripsi/spesifikasi/review di bawah.
- **Seller Detail:** Banner toko + info ringkas di atas, grid produk toko di bawah, tab "Produk / Tentang / Review".
- **Cart:** List item + summary card kanan (desktop) / summary sticky bottom (mobile).
- **Checkout:** Step terstruktur (Alamat → Pengiriman → Pembayaran → Review) dalam satu halaman dengan section collapsible, summary tetap terlihat di kanan/bottom.
- **Orders:** List card per order (status badge menonjol), klik → detail dengan timeline shipment (stepper horizontal/vertikal, lihat `USER-FLOW.md`).
- **Profile:** Sidebar sub-nav (Profil/Alamat/Pengaturan) + konten kanan, pola mirip dashboard ringan.

**Visual hierarchy:** Setiap halaman customer punya **1 fokus utama** (hero/product/CTA checkout) yang mendapat ukuran & whitespace terbesar; elemen sekunder (rekomendasi, filter) selalu lebih kecil skalanya.

---

## 8. Seller Dashboard Design

- **Sidebar:** ikon + label, grup: Dashboard, Produk, Pesanan, Inventory, Promosi, Analitik, Review, Pengaturan.
- **Topbar:** breadcrumb kiri, notifikasi bell + avatar toko kanan.
- **Dashboard home:** grid metric card (revenue, order baru, produk low-stock) + chart penjualan + activity feed pesanan terbaru.
- **Product management:** data table dengan thumbnail, filter status, bulk action (aktifkan/nonaktifkan), tombol "Tambah Produk" primary di kanan atas.
- **Forms (create/edit product):** multi-section form (Info dasar → Varian → Gambar → Harga & Stok → Pengiriman), disimpan sebagai draft otomatis.
- **Inventory:** table stok per varian dengan inline-edit quantity + indikator low-stock (badge warning).
- **Orders:** table sub-order dengan status badge, klik → detail order dengan aksi kontekstual (Konfirmasi/Input Resi/dst. sesuai status).
- **Analytics:** kombinasi chart (line untuk trend, bar untuk perbandingan produk) + tabel ringkas.

**Tone:** operational & efficient — data table & form mendominasi, whitespace tetap terjaga namun density lebih tinggi dibanding Customer surface.

---

## 9. Admin Dashboard Design

- **Sidebar:** grup: Dashboard, Users, Sellers, Products, Categories, Orders, Payments, Reviews, Reports, Disputes, Promotions, Analytics, Audit Log, Settings.
- **Topbar:** search global (cari user/order/produk cepat) + notifikasi + avatar admin.
- **Dashboard home:** metric card platform-wide (GMV, order hari ini, seller pending verifikasi, dispute terbuka) + chart tren + shortcut ke antrian moderasi.
- **Data table:** fitur lanjutan dibanding Seller (multi-filter, export, bulk action, kolom dapat disembunyikan).
- **Seller verification queue:** list card dengan dokumen preview + tombol Approve/Reject + field alasan.
- **Dispute center:** layout mirip inbox (list dispute kiri, thread percakapan kanan — mengikuti pola `dispute_messages`).
- **Reports/Analytics:** dashboard chart lebih kaya (multi-metric, filter tanggal custom).

**Tone:** powerful namun tetap terarah — setiap halaman data-berat tetap punya 1 primary action yang jelas (approve/reject/resolve), menghindari kesan "spreadsheet tanpa arah".

---

## 10. Responsive Design

### Mobile (< 768px)
- Nav → hamburger/bottom-nav (Customer: bottom tab bar untuk Home/Search/Cart/Orders/Profile).
- Grid produk → 2 kolom.
- Filter → drawer bottom, bukan sidebar.
- Checkout summary → sticky bottom bar (ringkas: total + tombol lanjut), detail breakdown via expand.
- Dashboard (Seller/Admin) → sidebar jadi off-canvas drawer, table → beralih ke stacked card per baris (untuk data yang tidak terlalu lebar) atau horizontal-scroll dengan kolom kunci sticky (untuk table lebar seperti order list).

### Tablet (768–1023px)
- Grid produk → 3 kolom.
- Sidebar dashboard → collapsible icon-only default, expand on-demand.
- Checkout → tetap 1 kolom namun summary pindah ke bawah tiap section (bukan sticky sidebar).

### Desktop (1024–1439px)
- Grid produk → 4 kolom.
- Sidebar dashboard → full (260px) default.
- Checkout/Product detail → 2 kolom sesuai §7.

### Large Desktop (≥1440px)
- Container tetap max-width (§5) — konten tidak melebar penuh, tambahan ruang jadi padding kiri-kanan.
- Grid produk dapat naik ke 5 kolom pada Customer discovery jika kepadatan konten mendukung.

---

## 11. Accessibility

- **Color contrast:** Teks body minimum rasio kontras 4.5:1 (WCAG AA), heading besar minimum 3:1 — palet §2 sudah divalidasi memenuhi ini untuk kombinasi teks-utama/background-utama.
- **Focus states:** Wajib visible pada seluruh elemen interaktif (§6.1), tidak boleh `outline: none` tanpa pengganti.
- **Keyboard navigation:** Seluruh flow utama (browse → cart → checkout, dashboard table → form) harus bisa dilakukan tanpa mouse; modal/drawer trap focus saat terbuka, `Esc` menutup.
- **Semantic HTML:** Heading hierarki benar, `<button>` untuk aksi bukan `<div onClick>`, `<nav>`/`<main>`/`<aside>` dipakai sesuai fungsi.
- **Form labels:** Setiap input punya `<label>` terasosiasi (bukan hanya placeholder).
- **Error messages:** Terhubung ke field via `aria-describedby`, tidak hanya mengandalkan warna merah (disertai ikon + teks).
- **Screen reader:** Ikon-only button (mis. cart icon) wajib `aria-label`. Status badge (mis. "Completed") harus terbaca sebagai teks, bukan hanya warna.
- **Touch target:** Minimum 44x44px pada seluruh elemen interaktif mobile.

---

## 12. UI States (Wajib untuk Setiap Major Page/Component)

| State | Perilaku Umum |
|---|---|
| Default | Tampilan normal dengan data ter-load. |
| Loading | Skeleton komponen (§6.4) menggantikan konten — bukan spinner full-page kecuali initial app load. |
| Empty | Ilustrasi/icon + pesan kontekstual ("Belum ada produk", "Cart kosong") + CTA relevan. |
| Error | Pesan error jelas + tombol retry, tidak menampilkan stack trace/technical detail ke user. |
| Success | Toast/inline confirmation, auto-dismiss untuk aksi ringan, tetap terlihat untuk aksi kritikal (order berhasil). |
| Disabled | Opacity 40%, cursor not-allowed, tidak menerima interaksi. |
| Hover | Elevation/scale/darken subtle sesuai komponen (§6). |
| Focus | Ring 2px `color-accent`, offset 2px. |
| Active | Darken/press-down subtle (scale 0.98 untuk button). |
| Selected | Border/background `color-primary` atau `color-accent` tergantung konteks (filter chip terpilih, tab aktif). |

---

## 13. Design Readiness Checklist

- [x] Design tokens konkret (warna hex, ukuran px, spacing scale) — bukan deskripsi abstrak.
- [x] Typography scale lengkap dengan ukuran desktop & mobile.
- [x] Component spec per elemen (variant/size/state).
- [x] Layout & grid spec per surface (Customer/Seller/Admin).
- [x] Responsive behavior per breakpoint dijelaskan per komponen, bukan generik.
- [x] Page inventory lengkap ada di `ROUTES.md` (saling silang referensi) dan screenshot final di `design/`.
- [x] Visual hierarchy & interaction pattern dijelaskan per grup halaman.
- [x] Reference design telah dianalisis (§0) dan diturunkan menjadi prinsip, bukan disalin mentah.
