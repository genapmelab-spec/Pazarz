# FLOW.md — User & Business Flow

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

## 3. Seller Flow

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

## 4. Admin Flow

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

## 5. Dispute Resolution Flow (Detail)

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

## 6. Notification Triggers (Ringkasan Lintas Flow)

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
