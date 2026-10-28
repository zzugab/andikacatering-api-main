# ANDIKA-100: Cara menelusuri dan bekerja

Status: workflow yang diturunkan dari struktur kode; bukan pengamatan lengkap
cara kerja pribadi pengguna. Lengkapi preferensi lewat `WORKING_MEMORY.md`.

## ANDIKA-101: Dari keluhan ke file

Minta gejala konkret jika belum tersedia: HTTP method/path, input tersanitasi,
hasil aktual, hasil yang diharapkan. Cari identifier persis sebelum pencarian
konsep umum. Jangan menebak endpoint berdasarkan nama controller.

```bash
rg -n 'employee|restrictRole|restrictStatus' routes/api.php app/Http/Kernel.php
rg -n 'function (indexUser|storeUser|updateUser)' app/Http/Controllers/UserController.php
rg -n 'function (store|edit|getById)|fillable|table' app/Models/UserModel.php
rg -n 'users|role_id|is_active' database/migrations
```

Ikuti keenam tahap: route → auth/status/role → validasi dan pemanggilan
controller → model dan kontrak hasil → relasi/schema/event → macro/output.
Cari semua pemanggil sebelum mengubah signature method model atau helper.

## ANDIKA-102: Peta entrypoint

Semua path di tabel relatif terhadap `/api`. Akses persis lihat `routes/api.php`.

| Gejala/fitur | Mulai menelusuri | Lanjut ke |
| --- | --- | --- |
| Login/token/akun beku | `AuthController::login`, `checkStatus`, `checkRole` | `UserModel`, `RolesModel`, `config/auth.php` |
| Pegawai `/employee`, profil `/user` | `UserController` | `UserModel`, `RolesModel`, `ActivityHelper` |
| Role lambat/data lama | `UserController::indexRole` | cache `user_roles_list`, TTL 3600 |
| Landing `/landingPage` | `PackageController::landingPage` | `PackagesModel`, `MenuModel`, `TestimoniModel`, cache TTL 300 |
| Paket dan kategori isian | `PackageController` | `PackagesModel`, `PackagesDataModel`, `PackageFoodStallModel` |
| Menu/resep/foto | `MenuController` | `MenuModel`, `MenuCategoryModel`, `RecipesModel`, `Images` |
| Customer/pencarian nama | `CustomerController` | `CustomerModel::getByName`, relasi `order` |
| Membuat order | `OrderController::storeWithCustomer` | `CustomerModel`, `OrdersCustomersModel`, `OrderDetailModel`, `PaymentsModel` |
| Menu pesanan/harga | `OrderController::storeMenus`, `updateMenus`, `addMenus` | `OrderPackageModel`, `OrderPackageRelModel`, `OrderPackageItems`, `OrderCustomizationModel`, `PaymentDetailModel` |
| Cicilan/pelunasan | `FinanceController::storeInstallment`, `updateInstallment`, `destroyInstallment` | `PaymentsModel`, `PaymentInstallmentsModel`, `Images` |
| Pemasukan/pengeluaran/refund | `FinanceController` | `FinancialRecordModel`, `PaymentsModel` |
| Gudang/transfer | `LogisticController` | `InventoryItemModel`, `EventTransferModel`, `EventTransferItemModel` |
| Selesai acara | `OrderController::eventEnd` | `EventLogisticModel`, stok, transfer, status payment/order |
| Jadwal meeting | `UserController` method `*Meet` | `MeetingScheduleModel`, waktu PHP dan PostgreSQL |
| PDF/tanggal/tampilan cetak | method `*Print` controller | `resources/views/*print*.blade.php`, `DateHelper.php` |

## ANDIKA-103: Cara membaca kegagalan berlapis

HTTP berhasil tidak membuktikan query berhasil. Baca envelope JSON luar dari
macro, lalu `status` dari model di dalam `data`, lalu nilai `data` model itu.
`getById()` dapat menghasilkan `status=true, data=null`; pemeriksaan status
saja tidak membuktikan resource ada. Model yang menangkap `Throwable` juga
dapat menelan exception sebelum sampai ke controller atau transaksi luar.

Jika gejala hanya muncul setelah refresh/deploy, periksa cache dan proses
Octane. Jika hanya muncul pada case nama tertentu, periksa operator `LIKE`
dan collation Postgres. Jika migrasi bersih gagal tetapi database lama bekerja,
bandingkan schema terpasang dengan migration, jangan langsung ubah query.

## ANDIKA-104: Siklus patch maintenance

1. Baca source method utuh beserta pemanggil dan modelnya. Ringkasan tidak cukup
   untuk menulis patch pada controller besar.
2. Tentukan behavior yang harus berubah dan kontrak yang tetap perlu dilayani.
3. Buat perubahan terkecil; refactor luas atau upgrade framework tugas terpisah.
4. Periksa sintaks file PHP yang berubah (`php -l path/file.php`). Gunakan test
   terarah untuk behavior, terutama uang, izin, stok, dan cascade delete.
5. Jalankan test database hanya di database khusus testing yang diverifikasi.
   `phpunit.xml` saat ini belum menentukan database terpisah.
6. Laporkan hasil aktual, keterbatasan, dan kebutuhan reload/cache bila relevan.
7. Refresh inventaris dan revisi knowledge jika kontrak berubah.

Jangan menjalankan migrasi/seed/reset database produksi sebagai cara mencoba
hipotesis. Jangan menyalin seluruh log atau `.env` untuk memberi konteks model.
