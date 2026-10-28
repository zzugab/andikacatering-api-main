# ANDIKA-200: Pola kode terverifikasi

Snapshot source 2026-10-27. Ini deskripsi pola aktual, bukan pengesahan semua
praktiknya sebagai best practice atau preferensi pribadi pengguna.

## ANDIKA-201: Controller dan model

Namespace controller `App\Http\Controllers`; model `App\Models`. Konstruktor
controller banyak mengisi properti private dengan `new ...Model()`, contohnya
`UserController::__construct` dan `OrderController::__construct`. Validasi
memakai `Illuminate\Support\Facades\Validator::make`; ambil
`$validator->validated()` setelah pemeriksaan `fails()`.

Query sering ditempatkan pada model, dengan `try/catch (Throwable $th)` dan
array hasil. Method helper model bukan Eloquent bawaan. Signature `edit`
bervariasi: `UserModel::edit(array $payload, string $id)` berlawanan urutan
dengan `PaymentsModel::edit(string $id, array $payload)`. Baca signature
setiap model; jangan melakukan penggantian massal berdasarkan nama method.

Beberapa `store()` memakai `firstOrCreate($payload)` terhadap seluruh payload.
Itu bukan jaminan idempotensi bisnis dan bukan operasi update. Jangan mengganti
menjadi `create()` atau `updateOrCreate()` tanpa memeriksa pemanggil dan unique
constraint. `first()->update()` dapat gagal saat record tidak ditemukan.

## ANDIKA-202: Kontrak response yang benar

Sumber: `app/Providers/AppServiceProvider.php::boot`.

```text
success($data = [], $message = '', $settings = [])
  HTTP 200; body {data: ..., message: ..., settings: ...}
failed($error = [], $httpCode = 422, $settings = [])
  HTTP $httpCode; body {errors: ..., settings: ...}
```

`response()->success($users, 200)` berarti field `message` bernilai angka 200.
Jika `$users` adalah hasil model, body bisa berisi `data.status` dan
`data.data`. Jangan meratakan nesting secara diam-diam. Tidak ada `success`
boolean otomatis pada macro tersebut. `AuthController` dan middleware juga
memiliki response JSON langsung dengan bentuk berbeda.

Untuk perubahan fitur kecil, pertahankan kontrak pemanggil. Usulan perbaikan
macro harus mengaudit seluruh call site dan frontend secara terpisah. HTTP
201 tidak dapat diperoleh hanya dengan mengganti argumen kedua `success`.

## ANDIKA-203: Identitas, relasi, soft deletes

Trait `App\Http\Traits\Uuid` mengisi atribut `uuid` pada event creating jika
kosong. Banyak model memakai `SoftDeletes`, menyembunyikan `id`, timestamp,
dan `deleted_at` dari serialisasi, tetapi tetap memakai primary key internal
default. Relasi sering menyebut `'uuid'` sebagai owner/local key eksplisit.

`CustomerModel::booted`, `OrdersCustomersModel::booted`,
`PaymentsModel::booted`, dan `OrderPackageRelModel::booted` memuat cascade
delete/restore. Bulk query delete dapat melewati event tiap instance. Jangan
memindahkan operasi tersebut ke SQL langsung tanpa memeriksa efek samping.

## ANDIKA-204: Helper dan bahasa

`ActivityHelper::log($description)` membuat record aktivitas dengan UUID user
saat login, atau null. Posisi log tidak seragam: ada pemanggilan sebelum
operasi selesai. Audit log bukan otomatis bukti write bisnis berhasil.

`DateHelper.php` mendefinisikan fungsi global `formatJamMenit` dan
`formatTanggalIndo`, dilindungi `function_exists`. Kosong atau parse gagal
menghasilkan `'-'`; tanggal Indonesia uppercase. Keduanya dimuat dari
`composer.json` bagian `autoload.files`, bersama `ActivityHelper.php`.

Penamaan identifier campuran Inggris dan Indonesia. Pertahankan ejaan nyata
seperti `payment_datelines`, `instalment`, `packages_id`, `testimoni`, dan
`totalIngridients` saat menyentuh kontrak terkait. Mengoreksi ejaan publik
memerlukan perubahan kontrak yang disengaja.

## ANDIKA-205: Batas konsistensi

Pola array hasil, status HTTP, urutan parameter, dan error handling belum
seragam. Jangan menjadikan ketidakkonsistenan itu aturan untuk kode baru.
Tiru pola file tetangga sepanjang benar, lalu jelaskan koreksi yang diperlukan
untuk bug tertentu. Tidak ada bukti bahwa pengguna meminta strict types,
Repository pattern, DTO, atau FormRequest untuk setiap maintenance.
