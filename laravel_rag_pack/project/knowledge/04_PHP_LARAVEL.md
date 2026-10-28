# ANDIKA-400: PHP dan Laravel sesuai proyek

Gunakan Laravel 13 dan PHP 8.5.12. Lock dan vendor lokal memuat Laravel 13.38.0;
struktur bootstrap tetap perlu dibaca dari proyek hasil upgrade ini. Rujukan
resmi di akhir tiap topik tidak otomatis tersedia offline bagi model.

## ANDIKA-401: Tipe PHP dan hasil operasi

Array PHP nonkosong bernilai truthy walaupun berisi `status=false`. Karena
method custom mengembalikan envelope, cek `($result['status'] ?? false)` dan
keberadaan `data` sesuai kontrak method. `isset` false untuk nilai null;
`array_key_exists` membedakan key hilang dan key bernilai null. `??` menjaga
akses optional tetapi jangan menutupi error alur wajib dengan default sembarang.

`Throwable` mencakup `Exception` dan `Error`. Catch lalu return array dapat
membuat caller tidak menyadari kegagalan. Property access pada null dan salah
urutan parameter perlu dicari saat error hanya terlihat sebagai response 422.
Jangan menambah strict types ke semua file sebagai efek samping bug fix.
Rujukan: [PHP types](https://www.php.net/manual/en/language.types.type-juggling.php).

## ANDIKA-402: Lifecycle dan konfigurasi proyek Laravel 13 hasil upgrade

`public/index.php` → bootstrap/container → `app/Http/Kernel.php` → route →
controller → model → response. Alias middleware ada di Kernel; provider route
menambahkan prefix `/api`, middleware API, dan limit 60 request/menit.
Macro response terdaftar di `AppServiceProvider::boot`.

`bootstrap/app.php` masih membuat Application dan mengikat kontrak HTTP Kernel
ke `App\Http\Kernel`. Keberadaan Kernel dan RouteServiceProvider bukan bukti
framework masih versi lama. Jangan menyalin skeleton Laravel 13 baru untuk
mengganti seluruh bootstrap hanya demi menyesuaikan contoh dokumentasi.

Gunakan `config()` saat mengakses konfigurasi aplikasi. Setelah config cache,
pembacaan `env()` di luar config dapat berbeda dari perkiraan. Proyek membaca
`CACHE_DRIVER` di `config/cache.php`; `CACHE_STORE` dalam README bukan key yang
dipakai konfigurasi ini. `config/app.php` menetapkan timezone `UTC`;
preferensi waktu pengguna Asia/Jakarta tidak otomatis mengubah timezone app.
Rujukan: [configuration](https://laravel.com/docs/13.x/configuration),
[middleware](https://laravel.com/docs/13.x/middleware).

## ANDIKA-403: Eloquent, UUID, dan relasi

`find()` memakai primary key model, bukan atribut yang disembunyikan dari JSON.
Jika model masih memakai `id`, gunakan query `where('uuid', $uuid)` seperti
method proyek. `with()` eager-load pada query; `load()` sesudah model/collection
didapat. Keduanya terlihat di flow landing. Jangan membuat N+1 dengan membaca
relasi per item tanpa mengecek eager loading.

`$fillable` mengatur mass assignment, bukan validasi atau izin. `SoftDeletes`
memberi global scope; `withTrashed`, `onlyTrashed`, `restore`, `forceDelete`
memiliki dampak berbeda. Event deleting/restoring proyek perlu ikut dipelajari.
Rujukan: [Eloquent](https://laravel.com/docs/13.x/eloquent),
[relationships](https://laravel.com/docs/13.x/eloquent-relationships).

## ANDIKA-404: Validasi dan otorisasi

Validasi tipe/format bukan pemeriksaan izin terhadap resource. Sanctum
mengautentikasi; middleware role/status membatasi akses route; akses per order
atau koordinator perlu dibaca pada method yang bersangkutan. Jangan menganggap
semua route yang memakai Sanctum hanya untuk Superadmin.

Pola saat ini Validator inline, banyak response gagal 400. `FormRequest`,
Policy, dan Resource adalah kemampuan framework yang dapat diusulkan untuk
kebutuhan tertentu, bukan class yang sudah terbukti ada di aplikasi ini.
Rujukan: [validation](https://laravel.com/docs/13.x/validation),
[Sanctum](https://laravel.com/docs/13.x/sanctum).

## ANDIKA-405: Transaksi dan error yang ditelan

Untuk beberapa write yang harus atomik, pertimbangkan `DB::transaction`.
Laravel rollback otomatis bila closure melempar exception. Jika helper model
menangkap exception dan mengembalikan `status=false`, caller perlu menghentikan
alur dengan benar agar transaksi tidak commit sebagian. Transaksi tidak
mengganti validasi bisnis atau menyelesaikan file upload rollback.
Sumber proyek: `OrderController::storeWithCustomer`, method cicilan finance.
Rujukan: [database transactions](https://laravel.com/docs/13.x/database#database-transactions).

## ANDIKA-406: Pengujian yang bermakna

Test saat ini hanya example unit true dan feature GET `/`; tidak membuktikan
order/pembayaran/role benar. Composer mendeklarasikan PHPUnit `^12.0`, lock
memuat **12.5.40**. Tes uang,
stock, cascade, query raw Postgres, dan izin memerlukan fixture serta database
testing terpisah. Jangan menambahkan `RefreshDatabase` sebelum memverifikasi
connection testing: konfigurasi saat ini belum mengunci database test.
Rujukan: [testing](https://laravel.com/docs/13.x/testing),
[database testing](https://laravel.com/docs/13.x/database-testing).

## ANDIKA-407: Upgrade dependency dan batas kesimpulan

Pengguna meng-upgrade karena bug PHP sebelumnya. Jenis bug dan reproduksinya
belum diberikan; jangan mengarang penyebab atau menyatakan bug pasti selesai
hanya karena versi berubah. PHP CLI lokal 8.5.12 dan Composer 2.10.3 sudah
diperiksa; itu tidak membuktikan image/container VM memakai patch yang sama.

`composer.json` masih menulis PHP `^8.1`, tetapi framework terkunci meminta
`^8.3`; versi efektif minimum dependency perlu dihitung dari lock. Tidak ada
perubahan constraint atau dependency otomatis oleh paket knowledge ini.

Untuk masalah setelah upgrade, cocokkan signature dan behavior dengan Laravel
13 serta versi package terpasang. Upgrade berhasil di Composer belum membuktikan
seluruh alur order, auth, PDF, dan pembayaran sudah diuji.
Rujukan: [upgrade Laravel 13](https://laravel.com/docs/13.x/upgrade).
