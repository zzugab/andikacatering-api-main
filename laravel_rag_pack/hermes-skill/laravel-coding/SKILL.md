---
name: laravel-coding
description: Panduan coding Laravel backend dengan referensi praktik framework, API, keamanan, testing, dan personal coding style yang telah diverifikasi dari repository.
version: 1.0.0
metadata:
  hermes:
    tags: [laravel, php, backend, coding]
    category: development
---

# Laravel Coding Companion

## When to use

Gunakan skill ini jika pengguna meminta penjelasan, review, debugging, perbaikan, atau implementasi kode Laravel/PHP.

## References

- Untuk penjelasan konsep Laravel, standar keamanan, API, testing, dan contoh: buka `skill_view("laravel-coding", "references/LARAVEL_RAG_KNOWLEDGE.md")`.
- Untuk aturan pribadi: buka `skill_view("laravel-coding", "references/PERSONAL_CODING_STYLE_TEMPLATE.md")`, tetapi **gunakan hanya bagian yang telah diperbarui berdasarkan kode project nyata dan disetujui pengguna**.
- Selalu periksa versi Laravel project dan gunakan dokumentasi resmi untuk detail yang sensitif terhadap versi: https://laravel.com/docs/13.x (ganti sesuai major version project).

## Procedure

1. Periksa jenis permintaan dan versi Laravel/PHP; bila tool file workspace tersedia, inspeksi `composer.json`, route, controller, model, dan test yang terkait.
2. Buka reference yang relevan menggunakan `skill_view` jika memerlukan panduan dari knowledge pack.
3. Ikuti implementation pattern existing. Jangan mengarang custom method atau response macro; verifikasi `response()->success()`/`failed()` sebelum mencontohkan.
4. Untuk perubahan kode, tampilkan file yang terpengaruh, implementasi yang dapat diuji, dan potensi masalah keamanan/kompatibilitas.
5. Sebutkan asumsi dan jangan mengklaim test lulus tanpa menjalankannya.

## Pitfalls

- Skill ini menyediakan akses ke file referensi yang dibaca Hermes saat dibutuhkan, **bukan vector RAG** dan **tidak otomatis membaca seluruh dokumentasi Laravel**.
- Knowledge Open WebUI adalah indeks tersendiri. Mengunggah file ke Open WebUI tidak otomatis memasangnya pada Hermes yang berjalan terpisah.
- Jangan menyalin data rahasia atau memodifikasi database produksi.

## Verification

Dalam sesi Hermes baru, minta: `Gunakan skill laravel-coding: jelaskan penggunaan FormRequest dan periksa gaya response macro saya.` Periksa apakah jawabannya membedakan perilaku Laravel standar dan custom project.
