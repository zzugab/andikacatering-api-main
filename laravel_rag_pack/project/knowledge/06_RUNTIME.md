# ANDIKA-600: Runtime, dependency, dan operasi

Snapshot konfigurasi repository; bukan laporan proses VM yang berjalan.

## ANDIKA-601: Docker, FrankenPHP, dan Octane

`Dockerfile`: `dunglas/frankenphp:1.13-php8.5-alpine`, extension pdo_pgsql,
pgsql, bcmath, gd, zip, pcntl; `octane:start --server=frankenphp --workers=4`
pada port 8000. Compose service `backend` menunggu DB healthy dan memuat
environment dari `.env`; DB `postgres:18` dengan named volume postgres_data.
Tidak ada bind mount source aplikasi pada service backend di snapshot Compose.

Mengubah checkout host tidak otomatis memperbarui source di image. Deployment
memerlukan build/recreate sesuai proses operator. Untuk source yang memang
terpasang pada worker yang sedang berjalan, Octane perlu reload agar kode
baru diambil. Jangan mengandalkan refresh browser untuk me-reload PHP worker.
Octane memelihara aplikasi di memory; hindari state request/user pada static
atau singleton yang terbawa ke request berikutnya.
Rujukan: [Octane Laravel 13](https://laravel.com/docs/13.x/octane).

PHP CLI pada workspace lokal terverifikasi 8.5.12; Composer 2.10.3. Docker tag
PHP 8.5 tidak membuktikan patch 8.5.12 di container. Versi VM/container perlu
dicek di prosesnya sendiri, dan tidak disimpulkan dari host atau ukuran tag.

## ANDIKA-602: Cache, waktu, dan data lama

Default cache memakai `CACHE_DRIVER` dengan fallback file. Key landing
`landing_page_data_` ditambah empat limit, TTL 300; role `user_roles_list`,
TTL 3600. Periksa invalidasi setelah write, bukan selalu menjalankan clear
global. Cache file tanpa volume dapat hilang saat container diganti.

`config/app.php` timezone UTC, sedangkan status meeting memakai jam database.
Bandingkan jam request, Carbon, PostgreSQL, dan browser ketika gejala dekat
pergantian tanggal. Jangan mengubah timezone global tanpa memeriksa kontrak.

## ANDIKA-603: Gambar dan PDF

`Images::store` memakai Intervention Image v3 + driver GD, menulis ke
`public/storage/uploads/{type}/...`. Link response pembayaran dibuat melalui
`Storage::url`. Periksa symlink `public/storage`, disk config, direktori, dan
permission bila upload sukses tetapi gambar tidak bisa diakses. Jangan
menyalin contoh API v2 `Image::make` sebagai API yang terbukti tersedia.

DomPDF memakai Blade server pada `resources/views`. Periksa data dari
controller, helper tanggal, format harga, serta asset/font ketika PDF salah.
Vite/axios ada pada package.json, tetapi aplikasi ini terutama backend;
keberadaan Vite bukan bukti frontend bisnis ada di repository ini.

## ANDIKA-604: Pemeriksaan operasional

Command berikut contoh untuk operator pada environment yang benar; belum
dijalankan terhadap VM. Jangan menempel output rahasia ke model.

```bash
php -v
composer show laravel/framework
php artisan route:list --path=api -v
docker compose ps
```

`route:list` mem-boot aplikasi dan dapat gagal jika dependency/provider belum
cocok. Gunakan inventaris statis sebagai penunjuk, bukan pengganti bukti runtime.
Sebelum test database, validasi environment khusus testing. Migrasi, seed,
cache clear global, build/deploy, dan reload worker memiliki dampak operasional;
bukan langkah wajib hanya untuk membaca knowledge.

`README.md` berisi panduan operasional umum. Selalu cocokkan dengan Dockerfile,
Compose, dan config aktual; misalnya key cache serta service db sudah berbeda
dari beberapa contoh umum pada README.
