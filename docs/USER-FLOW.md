# USER-FLOW.md — User & Business Flow

---

## 1. Authentication Flow

### 1.1 Customer Registration & Login (React)

```mermaid
flowchart TD
    A[Landing] --> B[Register Form]
    B --> C[Submit: name, email, password]
    C --> D[Send Verification Email]
    D --> E{Email Verified?}
    E -- No --> F[Show 'Resend Verification']
    E -- Yes --> G[Login]
    G --> H{Credentials Valid?}
    H -- No --> I[Show Error + Retry]
    H -- Yes --> J[Issue Session/Token]
    J --> K[Redirect to Home/Intended Page]
```

### 1.2 Seller Registration (Laravel Blade)

```mermaid
flowchart TD
    A[Seller Landing] --> B[Register as Seller]
    B --> C[Fill Business Info]
    C --> D[Submit Verification Documents]
    D --> E[Status: Pending Review]
    E --> F{Admin Review}
    F -- Approved --> G[Status: Verified]
    G --> H[Setup Store Profile]
    H --> I[Seller Dashboard Active]
    F -- Rejected --> J[Status: Rejected + Reason]
    J --> K[Seller Can Resubmit]
```

### 1.3 Password Reset (Semua Role)

```text
Request Reset (email)
→ Send Reset Link/Token
→ User Opens Link
→ Set New Password
→ Invalidate Old Sessions
→ Redirect to Login
```

**Edge cases:** token expired → minta request baru; email tidak terdaftar → tampilkan pesan generik (hindari user enumeration); token dipakai lebih dari sekali → ditolak.

---

## 2. Customer Flow (End-to-End Purchase)

```mermaid
flowchart LR
    Discover --> ProductDetail
    ProductDetail --> AddToCart
    AddToCart --> Cart
    Cart --> Checkout
    Checkout --> SelectAddress
    SelectAddress --> SelectShipping
    SelectShipping --> ApplyCoupon
    ApplyCoupon --> Payment
    Payment --> PaymentStatus
    PaymentStatus -- Success --> OrderConfirmed
    PaymentStatus -- Failed --> RetryPayment
    OrderConfirmed --> SellerProcessing
    SellerProcessing --> Shipped
    Shipped --> Delivered
    Delivered --> Completed
    Completed --> ReviewProduct
```

**Detail penting:**
- Checkout dapat mencakup item dari beberapa seller → sistem otomatis memecah menjadi beberapa `sub_order`, masing-masing dengan ongkir & status sendiri, namun **satu pembayaran** untuk seluruh `order`.
- Jika salah satu sub-order dibatalkan seller (mis. stok habis), order induk **tidak otomatis batal** — hanya sub-order tersebut yang membatalkan & memicu refund parsial.
- **Order Status States:** `pending_payment → paid → processing → shipped → completed` dengan cabang `cancelled` (bisa terjadi sebelum `shipped`) dan `disputed` (setelah `delivered`, sebelum `completed` final).

### 2.1 State Diagram — Order Status

```mermaid
stateDiagram-v2
    [*] --> pending_payment
    pending_payment --> paid: payment success
    pending_payment --> cancelled: payment expired/failed
    paid --> processing: seller confirms
    processing --> cancelled: seller cancels (stock issue)
    processing --> shipped: courier pickup
    shipped --> delivered: courier delivered
    delivered --> completed: auto after N days / customer confirms
    delivered --> disputed: customer raises dispute
    disputed --> completed: resolved in seller favor
    disputed --> cancelled: resolved in customer favor (refund)
    completed --> [*]
    cancelled --> [*]
```

---

## 3. Multi-Vendor Order Flow (Detail)

```mermaid
sequenceDiagram
    participant C as Customer
    participant L as Laravel (Checkout Service)
    participant S1 as Seller A
    participant S2 as Seller B
    participant PG as Payment Gateway

    C->>L: POST /checkout (cart berisi item dari Seller A & B)
    L->>L: Kelompokkan cart_items per store_id
    L->>L: Buat 1 order + 2 sub_orders (A, B) + order_items (snapshot harga)
    L->>L: Reservasi stok sementara per variant
    L->>PG: Buat payment intent (grand_total gabungan)
    PG-->>C: Instruksi pembayaran (VA/e-wallet/dsb.)
    C->>PG: Bayar
    PG-->>L: Webhook payment success
    L->>L: order.status = paid
    L->>S1: Notifikasi sub_order baru
    L->>S2: Notifikasi sub_order baru
    S1->>L: Confirm sub_order → processing → shipped
    S2->>L: Confirm sub_order → processing → shipped
    Note over S1,S2: Kedua sub-order berjalan independen;<br/>keterlambatan/pembatalan satu toko tidak memblokir toko lain.
```

**Catatan implementasi terkait:** lihat `DATABASE.md` §Entity Detail (`orders`, `sub_orders`, `order_items`) dan `ARCHITECTURE.md` §Shared Business Logic (kalkulasi harga, reservasi stok, perhitungan komisi).

---

## 4. Product Creation Flow (Seller)

```mermaid
flowchart TD
    A[Seller Dashboard] --> B[Klik 'Tambah Produk']
    B --> C[Isi Info Dasar: nama, deskripsi, kategori]
    C --> D[Pilih Atribut & Buat Varian]
    D --> E[Set Harga & SKU per Varian]
    E --> F[Upload Galeri Gambar]
    F --> G[Set Stok Awal per Varian]
    G --> H[Set Berat/Data Pengiriman]
    H --> I{Simpan sebagai?}
    I -- Draft --> J[Status: draft, belum tampil ke Customer]
    I -- Publikasikan --> K[Status: active, tampil di katalog]
    K --> L[Produk dapat dimoderasi Admin]
```

**Edge cases:** produk tanpa varian eksplisit tetap membuat 1 varian default agar konsisten dengan skema `product_variants`/`inventories`; produk dengan draft tidak masuk ke index pencarian.

---

## 5. Seller Flow (Operasional Harian)

```mermaid
flowchart TD
    A[Seller Registration] --> B[Verification]
    B --> C[Store Setup]
    C --> D[Add Products & Variants]
    D --> E[Set Inventory]
    E --> F[Store Goes Live]
    F --> G[Receive Order Notification]
    G --> H{Stock Available?}
    H -- Yes --> I[Confirm Sub-Order]
    H -- No --> J[Cancel Sub-Order + Notify Customer]
    I --> K[Prepare & Pack]
    K --> L[Input Shipment / Print Label]
    L --> M[Handover to Courier]
    M --> N[Status: Shipped]
    N --> O[Status: Delivered]
    O --> P[Sub-Order Completed]
    P --> Q[Revenue Reflected in Analytics]
```

**Edge cases:** partial stock (sebagian item dalam sub-order out of stock) → seller dapat mem-flag item spesifik untuk dibatalkan tanpa membatalkan seluruh sub-order (jika kebijakan platform mengizinkan; didokumentasikan sebagai opsi di `API.md`).

---

## 6. Admin Flow

```mermaid
flowchart TD
    A[Login] --> B[Admin Dashboard]
    B --> C[Monitor Platform Metrics]
    B --> D[Manage Users]
    B --> E[Review Seller Applications]
    B --> F[Manage Categories & Products]
    B --> G[Monitor Orders & Payments]
    B --> H[Moderate Reviews & Reports]
    B --> I[Handle Disputes]
    B --> J[Manage Promotions]
    B --> K[View Reports & Analytics]
    B --> L[Configure Platform Settings]
    E -->|Approve/Reject| M[Notify Seller]
    H -->|Action: Hide/Warn/Suspend| N[Log to Audit Log]
    I -->|Mediate| O[Resolve: Refund / Reject / Escalate]
```

---

## 7. Order Processing Flow — Dispute Resolution (Detail)

```mermaid
sequenceDiagram
    participant C as Customer
    participant S as Seller
    participant A as Admin
    C->>A: Raise dispute (sub_order, reason, evidence)
    A->>S: Notify seller of dispute
    S->>A: Submit response/evidence
    A->>A: Review evidence from both sides
    alt Resolved in customer's favor
        A->>C: Approve refund
        A->>S: Notify resolution
    else Resolved in seller's favor
        A->>S: Confirm no action needed
        A->>C: Notify resolution + reasoning
    else Needs more info
        A->>C: Request additional evidence
        A->>S: Request additional evidence
    end
```

---

## 8. Notification Triggers (Ringkasan Lintas Flow)

| Event | Penerima |
|---|---|
| Order dibuat & dibayar | Customer, Seller (per sub-order) |
| Sub-order dikonfirmasi/dibatalkan | Customer |
| Status pengiriman berubah | Customer |
| Order selesai | Customer (untuk review), Seller (revenue) |
| Review baru masuk | Seller |
| Seller mengajukan pendaftaran | Admin |
| Seller disetujui/ditolak | Seller |
| Dispute diajukan | Admin, Seller |
| Dispute diselesaikan | Customer, Seller |
| Laporan (report) baru masuk | Admin |
