# ANDIKA-000: Konteks minimum proyek

Audit source: 2026-10-27. Backend API Andika Sari Catering. Sumber aktual lebih
kuat daripada dokumen ini; bedakan fakta source, usulan, dan runtime belum dicek.

- `composer.lock` dan metadata vendor lokal: Laravel **13.38.0**.
  Lock: Sanctum **4.3.3**, Octane **2.22.0**, DomPDF integration **3.1.2**,
  Intervention Image **3.11.10**, PHPMailer **6.12.0**, Guzzle **7.16.0**,
  Ramsey UUID **4.9.4**, PHPUnit **12.5.40**.
- Pengguna mengonfirmasi upgrade ke Laravel 13 dan PHP **8.5.12**. CLI lokal
  terverifikasi PHP **8.5.12**, Composer **2.10.3**; runtime VM belum diinspeksi.
- `composer.json`: Laravel `^13.30`, PHP masih `^8.1`; Laravel yang terkunci
  mensyaratkan PHP `^8.3`. Dockerfile PHP **8.5**, FrankenPHP **1.13**;
  Compose PostgreSQL **18**. Tag Docker tidak menetapkan patch PHP persis.
- Versi dependency lengkap: `knowledge/generated/STACK.md` dari `refresh`.
  Struktur bootstrap masih mengikat `App\Http\Kernel` di `bootstrap/app.php`;
  jangan menyimpulkan versi framework dari struktur folder lama.
- Route API `routes/api.php`; prefix `/api` dari `RouteServiceProvider`.
- Controller langsung memakai model custom; model menyimpan query dan method
  `getAll`, `getById`, `store`, `edit`, `drop`. Jangan mengarang `App\Models\User`
  atau `Order`: yang dipakai `UserModel` dan `OrdersCustomersModel`.
- Banyak model mengembalikan array `status/data` atau `status/error`. Array
  `['status' => false, ...]` tetap truthy. Periksa isi dan nilai null `data`.
- Identitas publik umumnya `uuid`, sementara primary key internal dapat `id`.
  Baca relasi dan migration; jangan menganggap `find($uuid)` benar.
- Auth `auth:sanctum`, akun aktif `restrictStatus:true`, role `restrictRole`.
  Sebagian route di luar grup role; jangan menggeneralisasi izin seluruh API.
- Macro `success($data, $message, $settings)` selalu HTTP 200. Argumen kedua
  **message**, bukan HTTP status. Macro `failed($error, $httpCode, $settings)`
  memakai argumen kedua sebagai HTTP status. Sumber `AppServiceProvider::boot`.
- Validasi umumnya `Validator::make`, error validasi banyak memakai HTTP 400;
  jangan mengubah kontrak global ketika memperbaiki satu fitur.
- `ActivityHelper::log`, trait `Uuid`, soft deletes, Blade PDF, dan cache turut
  memengaruhi behavior. Perubahan data bisa memicu cascade event model.
- Tidak ditemukan layer `app/Services`, `Http/Requests`, `Http/Resources`, atau
  `Policies` pada snapshot ini. Membuatnya adalah perubahan arsitektur baru.
- Jangan memasukkan rahasia, data pelanggan, dump SQL, token, atau log mentah
  ke prompt/KB. Tidak ada data database hidup yang diverifikasi oleh audit ini.

Alur kerja: gejala + HTTP method + path → route dan middleware → method
controller → signature model → relasi/migration → helper/cache/file → pemeriksaan
terarah. Jawaban menyebut `path::method`, fakta yang didukung, bagian yang belum
diketahui, patch kecil jika diminta, dan cara verifikasi. Jangan mengaku telah
membaca file atau menjalankan test jika hanya menerima ringkasan.
