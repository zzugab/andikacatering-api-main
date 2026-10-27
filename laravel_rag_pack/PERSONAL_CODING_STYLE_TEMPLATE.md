# Personal Laravel Coding Style - Template yang Harus Diisi dari Repository

> STATUS: TEMPLATE, BELUM MENJADI CODING STYLE PRIBADI YANG TERKONFIRMASI.
> Fungsi: menjadi referensi prioritas tinggi untuk asisten Laravel lokal setelah diisi dari project nyata dan direview pengguna.
> Jangan menganggap placeholder sebagai fakta.

## STYLE-001: Identitas dan cakupan

- Nama project yang dijadikan referensi: `ISI`
- Commit/revision yang diaudit: `ISI`
- Laravel dan PHP version: `ISI`
- Jenis project: API / monolith / SSR / microservice / lainnya: `ISI`
- Preferensi bahasa jawaban: Indonesia (sementara)
- Bahasa nama class, method, variable: Inggris (sementara)

## STYLE-002: Format dan bahasa PHP

- Formatter (Laravel Pint/PSR-12/custom): `BELUM_DIKONFIRMASI`
- Penggunaan `strict_types`: `BELUM_DIKONFIRMASI`
- Penggunaan constructor property promotion: `BELUM_DIKONFIRMASI`
- Return types: `BELUM_DIKONFIRMASI`
- Tipe test runner dan standar lint: `BELUM_DIKONFIRMASI`

Aturan: ikuti project existing, jangan memaksakan refactor hanya karena gaya umum.

## STYLE-003: Arsitektur project

- Controller menangani: `ISI_BERDASARKAN_CONTOH`
- Validasi melalui: `FormRequest | validate() | lain: ISI`
- Logic bisnis melalui: `Service | Action | Controller | lain: ISI`
- Database melalui: `Eloquent | Repository | QueryBuilder | lain: ISI`
- Formatter API: `Resource | response macro | custom helper | lain: ISI`
- Auth: `ISI`
- Exception handling: `ISI`

## STYLE-004: API Response Macro (indikasi dari percakapan; perlu inspeksi source)

Contoh yang pernah digunakan:
```php
return response()->success($data, 200);
return response()->failed('Something error', 400);
```

**Belum terbukti** bahwa response macro ini terdaftar pada seluruh project. Periksa kode yang memanggil `Response::macro('success', ...)` dan `Response::macro('failed', ...)` atau registrasi setara. Catat signature, response JSON, status dan lokasi registrasi berikut:

- Lokasi macro: `ISI_PATH`
- Signature `success`: `ISI`
- Signature `failed`: `ISI`
- Contoh body JSON success: `ISI`
- Contoh body JSON failed: `ISI`
- Kapan memakai HTTP 200, 201, 400, 401, 403, 404, 422, 500: `ISI`

Jangan menambahkan field `status`, `code`, `data`, `message` jika kontrak asli tidak menunjukkannya.

## STYLE-005: Folder dan naming convention

- Root namespace controller: `ISI`
- Lokasi service: `ISI`
- Lokasi DTO: `ISI_ATAU_TIDAK_ADA`
- Lokasi custom exception: `ISI`
- Pola penamaan request/resource/test: `ISI`
- Format route: `ISI`
- Format timestamp, currency, timezone: `ISI`

## STYLE-006: Contoh nyata yang direview dan dianggap canonical

### Contoh A: Endpoint GET
- Referensi file: `app/Http/Controllers/...`
- Ringkasan alasan contoh ini mencerminkan style: `ISI`
- Potongan kode yang disetujui (pastikan tidak mengandung secrets):
```php
// Tempel potongan kode nyata hasil review di sini.
```

### Contoh B: Endpoint POST + validation + response
- Referensi file: `ISI`
```php
// Tempel contoh yang sudah disetujui.
```

### Contoh C: Service dan Eloquent query
- Referensi file: `ISI`
```php
// Tempel contoh yang sudah disetujui.
```

### Contoh D: Feature test
- Referensi file: `ISI`
```php
// Tempel contoh yang sudah disetujui.
```

## STYLE-007: Larangan pribadi / anti-pattern project

- `ISI_JIKA_ADA` (contoh: jangan membuat Repository class tanpa alasan)
- `ISI_JIKA_ADA` (contoh: jangan menulis SQL mentah untuk query sederhana)
- Jangan menyimpan password, API key, token, nilai dari `.env`, atau data pelanggan pada file ini.

## STYLE-008: Checklist sebelum jawaban kode diberikan

1. Apakah versi Laravel/PHP sesuai project?
2. Apakah struktur folder, nama class, dan namespace mengikuti project?
3. Apakah API response memakai formatter yang benar-benar ada?
4. Apakah validasi, auth, policy, dan akses tenant sudah diperiksa?
5. Apakah ada N+1, mass assignment, SQL injection, race condition, atau data leak?
6. Apakah contoh dilengkapi test dan cara verifikasi?
7. Apakah asumsi dan bagian yang belum terkonfirmasi disebutkan?

## STYLE-009: Log perubahan

- 2026-10-27: Dokumen template awal dibuat; belum diisi berdasarkan repository.
