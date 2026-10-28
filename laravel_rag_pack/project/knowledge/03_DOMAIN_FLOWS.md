# ANDIKA-300: Alur domain catering

Sumber utama: controller dan model yang disebut di setiap bagian. Snapshot
2026-10-27; ini alur implementasi yang terlihat, bukan spesifikasi bisnis lengkap.

## ANDIKA-301: Customer dan pembuatan order

`POST /api/order` → `OrderController::storeWithCustomer`. Route berada di grup
auth + status aktif + role Superadmin/Admin/Akuntan. Method ini menerima field
customer, order, dan detail acara sekaligus. Customer dicari berdasarkan
`customer_id` + nama bila ID tersedia, lalu nama saja, lalu dibuat bila belum
ada. Nama customer bukan bukti identitas unik; jangan mengubah matching tanpa
mengetahui aturan bisnis pengguna.

Urutan write: customer → `OrdersCustomersModel::store` →
`OrderDetailModel::store` → `PaymentsModel::store` dengan status `not finished`
→ baca ulang order → log aktivitas → response. Method ini tidak membungkus
seluruh write dalam transaksi. Hasil tiap model harus diperiksa; return array
gagal tidak otomatis menjadi exception.

`OrdersCustomersModel::store` membuat nomor `ORD-YYYYMMDD-NNN` dari jumlah
record hari ini + 1. Concurrent request dan soft delete perlu diuji; count+1
bukan alokasi nomor yang aman terhadap race condition dengan sendirinya.

## ANDIKA-302: Paket template dan menu pada order

Template: `PackagesModel` (`packages`) → `packageData()` ke
`PackagesDataModel` (`packages_data`) → kategori; `packageFoodStall()` ke
`PackageFoodStallModel` → menu. Paket template dan paket dalam order adalah
objek berbeda.

Pesanan: `OrdersCustomersModel::orderPackage()` → `OrderPackageModel`
(`order_packages`) → `orderPackageRel()` ke `OrderPackageRelModel`
(`order_package_rel`) → `OrderPackageItems` (`order_package_items`).
Menu tambahan ada di `OrderCustomizationModel`. Telusuri method `storeMenus`,
`updateMenus`, `listMenu`, `addMenus`, dan `restore*Menu` pada `OrderController`
sebelum mengubah perhitungan jumlah/harga.

`OrderPackageRelModel::booted` mengecek pemakaian item oleh relasi lain sebelum
menghapus item; restore dapat mengembalikan item soft deleted. Jangan
menganggap item selalu eksklusif milik satu relasi.

## ANDIKA-303: Payment, rincian, dan cicilan

`OrdersCustomersModel::payment()` adalah hasOne ke `PaymentsModel` memakai
`order_id → uuid`; `PaymentsModel::installment()` dan `paymentDetails()`
masing-masing ke `PaymentInstallmentsModel` dan `PaymentDetailModel`.
`PaymentDetailModel` berisi `payment_id`, `type`, `price`, `details`.

`FinanceController::storeInstallment`: validasi bank/amount/tanggal/gambar →
baca total tagihan → jumlahkan cicilan → hitung sisa → tentukan tipe
`payment installments` atau `final payment` → simpan gambar → simpan cicilan
→ jika lunas ubah payment menjadi `completed` → log dan link gambar.
Jika amount tagihan null, kode memilih `payment installments`.

`destroyInstallment` melakukan soft delete cicilan lalu menghitung ulang saldo,
tipe cicilan terakhir, dan status payment (`not finished`/`completed`).
Perubahan create/update/delete cicilan perlu diuji bersama perhitungan ulang,
upload, dan concurrent write. File storage tidak otomatis rollback ketika
transaksi database rollback. `sumByPayment` mengikuti scope soft delete model.

Sumber enum migration payment: `not finished`, `completed`, `refund`,
`cancelled`. Jangan menganggap status order memakai enum yang sama.

## ANDIKA-304: Logistik dan selesai acara

Gudang: `InventoryItemModel`. Barang acara: `EventLogisticModel`. Transfer:
`EventTransferModel` dan `EventTransferItemModel`. Masuk dari
`LogisticController` atau method logistik `OrderController`.

`OrderController::eventEnd` menyesuaikan jumlah barang acara dan stok gudang
berdasarkan barang tidak kembali/masih pada klien, lalu melihat status payment
untuk menentukan status order. Uji pemanggilan ulang, stock underflow, transfer,
dan perubahan payment setelah acara selesai. Kode menulis `event_finish`
untuk payment completed; nilai itu tidak tercantum pada enum migration order
yang ada. Ini ketidaksesuaian source, bukan bukti constraint database hidup.

## ANDIKA-305: Pembacaan publik, cache, dan PDF

`PackageController::landingPage`: empat limit membentuk cache key
`landing_page_data_...`, TTL 300 detik. Mengambil paket, menu `Buffet`, menu
`Foodstall`, dan testimonial, termasuk relasi kategori/stall. Data lama setelah
edit bisa berasal dari cache; invalidasi dan TTL harus dipertimbangkan.

PDF memakai `Barryvdh\DomPDF\Facade\Pdf::loadView`:
`orderPrint` → `order-print-layout`; `printByPeriod` →
`order-print-monthly-layout`; `purchasePlanPrint` → `purchaseplan-print-layout`;
`logisticPrint` → `eventInventory-print-layout`; `invoicePrint` →
`invoice-print-layout`; `installmentDetailPrint` → `payment-print-layout`.
View ada di `resources/views/*.blade.php`; jangan mencari frontend SPA untuk
memperbaiki layout cetak server ini.

## ANDIKA-306: Login, role, dan akses

`AuthController::login` menerima `namemail` (email atau username) dan password,
memeriksa `is_active`, lalu membuat token Sanctum. Provider auth menunjuk
`UserModel`. `logout` menghapus **semua token** user, bukan hanya token aktif.

Nama role yang muncul pada route: `Superadmin`, `Admin`, `Akuntan`.
`checkRole` membaca `role_id` lalu nama dari `RolesModel`. Route order baca,
logistik, beberapa write stok, dan PDF di bagian akhir file berada di luar
subgrup role tetapi masih di grup auth/status aktif. Izin berbeda per route;
periksa cakupan kurung grup sebelum menyimpulkan hak akses.
