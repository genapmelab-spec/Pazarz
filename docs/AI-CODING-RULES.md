# AI-CODING-RULES.md — Rules for AI Coding Agent

Aturan berikut wajib diikuti oleh AI coding agent (atau developer manapun) yang mengerjakan implementasi Pazarz pada fase development, setelah dokumentasi ini disetujui.

---

1. **Read documentation before coding.** Baca `PRD.md`, `FEATURES.md`, `ARCHITECTURE.md`, `DATABASE.md`, `API.md`, `DESIGN.md`, `ROUTES.md`, dan `USER-FLOW.md` yang relevan sebelum menulis kode apa pun untuk suatu fitur.

2. **React is Customer only.** Seluruh UI React + Vite hanya untuk permukaan Customer (lihat `ROUTES.md` §A). Jangan gunakan React untuk Seller atau Admin.

3. **Blade is Admin + Seller.** Seluruh UI Seller dan Admin memakai Laravel Blade (lihat `ROUTES.md` §B–C). Jangan gunakan Blade untuk permukaan Customer publik.

4. **Laravel owns business logic.** Validasi, kalkulasi harga, perubahan status order, dan logic bisnis lain ditempatkan di layer Service/Action Laravel (`ARCHITECTURE.md` §2), bukan diduplikasi di Controller API dan Controller Blade, dan bukan dihitung ulang di sisi client (React/Blade).

5. **Never trust client-side price.** Harga final selalu dihitung ulang di server saat checkout (`ARCHITECTURE.md` §9); harga yang dikirim dari client hanya untuk display, tidak pernah dipakai sebagai sumber kebenaran transaksi.

6. **Never trust client-side user ID.** User ID/identitas selalu diambil dari token/session terautentikasi di server, tidak pernah dari payload request.

7. **Never trust client-side seller ID / store ID.** Store ID yang memiliki suatu resource (produk, sub-order) selalu divalidasi dari relasi `seller_id`/`store_id` milik user yang login di server, tidak dari payload client.

8. **Enforce authorization server-side.** Setiap endpoint API dan setiap route Blade yang butuh permission wajib dicek lewat Middleware/Gate/Policy di server (`ARCHITECTURE.md` §7.5–7.9), tidak cukup hanya menyembunyikan tombol di UI.

9. **Enforce Seller ownership.** Seller hanya boleh mengelola resource miliknya sendiri (`ARCHITECTURE.md` §7.6 Resource Ownership Rules) — dicek via Policy per model, bukan hardcode di Controller.

10. **Follow API contract.** Struktur request/response harus sesuai `API.md`; jangan menambah/menghapus field response tanpa memperbarui dokumentasi terlebih dahulu.

11. **Do not redesign provided UI.** Jangan mengganti layout, component hierarchy, visual style, atau UX flow yang sudah ditentukan di design image (`design/`) dan `ROUTES.md`, kecuali diminta secara eksplisit — lihat `DECISIONS.md` §Conflict Rule.

12. **Use design images as visual implementation reference.** Design image di `design/` adalah **visual target final** untuk layout, spacing aktual, dan component placement. `DESIGN.md` adalah **design-system reference** untuk aturan yang tidak terlihat jelas dari gambar (token warna, ukuran spesifik, state interaktif). Jika ada konflik antara keduanya, gambar desain yang diikuti untuk aspek visual, sedangkan `DESIGN.md` tetap dipakai untuk hal yang gambar tidak dapat tunjukkan (mis. warna hex persis, perilaku hover/focus).

13. **Do not add features without approval.** Jangan mengimplementasikan fitur di luar `FEATURES.md` (🟢 MVP) tanpa instruksi eksplisit — fitur 🔵 Future tidak dikerjakan pada fase MVP.

14. **Implement incrementally.** Ikuti urutan fase di `IMPLEMENTATION-PLAN.md`; jangan mengerjakan fase yang dependency-nya belum selesai (mis. Checkout sebelum Cart & Database siap).

15. **Test meaningful changes.** Business logic kritikal (kalkulasi harga, split sub-order, reservasi stok, webhook idempotency, authorization policy) wajib memiliki test — lihat `IMPLEMENTATION-PLAN.md` §Phase 10.

16. **Report conflicts instead of silently guessing.** Jika ditemukan requirement yang bertentangan antar dokumen, atau antara design image dengan business/technical requirement (authentication, authorization, database rules, API contract, security, ownership), **laporkan konfliknya** — jangan menebak atau menyelesaikan sendiri secara diam-diam.

---

## Catatan Tambahan

- Business logic (§4–9) selalu lebih tinggi prioritasnya daripada preferensi visual apa pun dari design image — design image tidak boleh mengubah business rules, authentication, authorization, database rules, API contract, security, user ownership, atau seller ownership (lihat `DECISIONS.md`).
- Pada fase dokumentasi ini (sebelum `IMPLEMENTATION-PLAN.md` Phase 1 dimulai), **tidak ada kode implementasi yang boleh ditulis** — tidak ada migration, model, controller, komponen React, komponen Blade, atau implementasi API. Lihat `README.md` §Development Workflow.
