# API.md — API Specification (Customer-facing REST API)

> Dokumen ini adalah spesifikasi untuk fase development. Tidak ada implementasi kode di sini. API ini terutama melayani **React Customer app**; Seller & Admin Dashboard menggunakan Blade langsung (lihat `ARCHITECTURE.md`), namun beberapa endpoint internal setara dapat direuse sebagai JSON API bila dibutuhkan (mis. untuk widget async di dashboard).

---

## 1. API Conventions

- **Base URL:** `https://api.pazarz.com/api/v1`
- **Format:** JSON request & response (`Content-Type: application/json`).
- **Versioning:** path-based, `/api/v1/...`. Breaking change → naikkan ke `/api/v2/`.
- **Auth:** Bearer token di header `Authorization: Bearer {token}` (lihat `AUTH.md`).

## 2. Response Format

**Success:**
```json
{
  "success": true,
  "data": { },
  "meta": { }
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": { "email": ["Email sudah terdaftar."] }
  }
}
```

## 3. Error Format & HTTP Status Codes

| Status | Kode Umum | Kapan Dipakai |
|---|---|---|
| 200 | — | Sukses (GET/PUT/PATCH/DELETE) |
| 201 | — | Resource berhasil dibuat |
| 400 | `BAD_REQUEST` | Request tidak valid secara umum |
| 401 | `UNAUTHENTICATED` | Token tidak ada/invalid |
| 403 | `FORBIDDEN` | Tidak punya permission/ownership |
| 404 | `NOT_FOUND` | Resource tidak ditemukan |
| 409 | `CONFLICT` | Mis. stok berubah saat checkout |
| 422 | `VALIDATION_ERROR` | Payload gagal validasi |
| 429 | `TOO_MANY_REQUESTS` | Rate limit terlampaui |
| 500 | `SERVER_ERROR` | Kesalahan tak terduga di server |

## 4. Pagination

Cursor atau page-based (page-based dipilih untuk simplicity di katalog):
```text
GET /products?page=2&per_page=24
```
Response `meta`:
```json
{ "current_page": 2, "per_page": 24, "total": 480, "last_page": 20 }
```

## 5. Filtering, Sorting, Searching

```text
GET /products?category=streetwear&min_price=100000&max_price=500000&rating_min=4
GET /products?sort=price_asc | price_desc | newest | best_selling | rating
GET /products?q=hoodie
```

---

## 6. Endpoint Groups

```text
/api/v1/auth
/api/v1/products
/api/v1/categories
/api/v1/stores
/api/v1/cart
/api/v1/wishlist
/api/v1/checkout
/api/v1/orders
/api/v1/reviews
/api/v1/notifications
/api/v1/profile
/api/v1/addresses
```

---

## 7. Endpoint Detail (Representatif)

### 7.1 `POST /auth/register`
- **Purpose:** Registrasi akun customer baru.
- **Auth:** Tidak perlu.
- **Body:** `{ name, email, password, password_confirmation }`
- **Response 201:** `{ user }` + email verifikasi dikirim async.
- **Errors:** 422 (email sudah terdaftar / validasi format).

### 7.2 `POST /auth/login`
- **Purpose:** Login customer.
- **Auth:** Tidak perlu.
- **Body:** `{ email, password }`
- **Response 200:** `{ user, token }`
- **Errors:** 401 (kredensial salah), 429 (terlalu banyak percobaan).

### 7.3 `GET /auth/me`
- **Purpose:** Ambil data user yang sedang login (hydrate state React).
- **Auth:** Bearer token required.
- **Response 200:** `{ user, permissions }`

### 7.4 `GET /products`
- **Purpose:** List/search produk dengan filter, sort, pagination.
- **Auth:** Publik.
- **Parameters:** `q, category, min_price, max_price, rating_min, sort, page, per_page`
- **Response 200:** array produk + `meta` pagination.

### 7.5 `GET /products/{slug}`
- **Purpose:** Detail produk termasuk varian, gambar, review summary, data toko.
- **Auth:** Publik.
- **Response 200:** `{ product, variants, images, store, reviews_summary }`
- **Errors:** 404 jika produk tidak ada/nonaktif.

### 7.6 `POST /cart/items`
- **Purpose:** Tambah item ke cart.
- **Auth:** Required.
- **Body:** `{ product_variant_id, quantity }`
- **Response 201:** cart terbaru.
- **Errors:** 409 jika stok tidak mencukupi.

### 7.7 `PATCH /cart/items/{id}`
- **Purpose:** Update quantity item cart.
- **Auth:** Required (ownership: cart milik user).
- **Body:** `{ quantity }`
- **Errors:** 403 jika cart bukan milik user, 409 jika melebihi stok.

### 7.8 `POST /checkout`
- **Purpose:** Membuat order dari isi cart (memecah menjadi sub_orders per seller), menghasilkan intent pembayaran.
- **Auth:** Required.
- **Body:** `{ shipping_address_id, shipping_method_per_store, coupon_code? }`
- **Response 201:** `{ order, payment_instructions }`
- **Errors:** 409 (stok berubah sejak cart diisi), 422 (kupon tidak valid/kadaluarsa).
- **Side effects:** reservasi stok sementara, kalkulasi ongkir & diskon final, snapshot harga ke `order_items`.

### 7.9 `POST /checkout/payment-callback` *(internal, dipanggil oleh Payment Gateway webhook)*
- **Purpose:** Update status pembayaran berdasarkan callback provider.
- **Auth:** Signature verification dari provider (bukan user token).
- **Response 200:** ack ke provider.
- **Side effects:** update `payments.status`, jika sukses → update `orders.status = paid`, trigger notifikasi ke seller terkait.

### 7.10 `GET /orders`
- **Purpose:** List order milik customer yang login.
- **Auth:** Required.
- **Response 200:** array order + status ringkas per sub-order.

### 7.11 `GET /orders/{order_number}`
- **Purpose:** Detail order termasuk seluruh sub-order, item, shipment status.
- **Auth:** Required (ownership check).
- **Errors:** 403 jika bukan order milik user, 404 jika tidak ditemukan.

### 7.12 `POST /orders/{order_number}/complete`
- **Purpose:** Customer mengonfirmasi barang diterima (mempercepat status dari `delivered` ke `completed`).
- **Auth:** Required (ownership).

### 7.13 `POST /reviews`
- **Purpose:** Membuat review untuk item yang sudah `completed`.
- **Auth:** Required.
- **Body:** `{ order_item_id, rating, comment, images[] }`
- **Errors:** 422 jika `order_item_id` belum eligible (belum completed / sudah direview).

### 7.14 `POST /disputes`
- **Purpose:** Mengajukan dispute terhadap sub-order.
- **Auth:** Required (ownership terhadap order terkait).
- **Body:** `{ sub_order_id, reason, description, attachments[] }`

---

## 8. Catatan Konsistensi

- Seluruh endpoint yang butuh ownership check merujuk pada aturan di `ROLES.md` § Resource Ownership Rules.
- Struktur response `product`, `order`, dst harus konsisten dengan struktur entity di `ERD.md` — API Resource (transformer) tidak boleh menciptakan field yang tidak berdasar dari model.
- Endpoint checkout & payment callback adalah titik paling kritikal — wajib idempotent (webhook yang terpanggil dua kali tidak boleh memproses pembayaran dua kali), didokumentasikan lebih detail saat implementasi.
