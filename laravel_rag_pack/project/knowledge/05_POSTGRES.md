# ANDIKA-500: PostgreSQL dalam proyek

Sumber: `config/database.php`, `docker-compose.yml`, model, migration. Default
driver konfigurasi `pgsql`; Compose memakai `postgres:18`. Versi, constraint,
index, jumlah row, timezone, dan schema database VM belum diperiksa.

## ANDIKA-501: UUID dan schema yang belum selaras

Pola umum migration adalah `$table->id()` ditambah `$table->uuid('uuid')`;
foreign key aplikasi sering menyimpan UUID. Jangan join `*_id` ke integer `id`
tanpa membaca deklarasi relasi. `$hidden=['id']` tidak mengubah primary key.

Ada drift: migration users memiliki `role` sementara model memakai `role_id`
dan `is_active`; model `order_package_rel` dan `payment_details` tidak memiliki
migration pembuat tabel pada snapshot. Migration merupakan bukti deklarasi
versioned, bukan bukti schema terpasang sudah cocok. Dump bisa berisi data
pribadi; jangan memasukkannya ke KB untuk mengetahui schema.

Pemeriksaan manual read-only oleh operator pada connection yang benar:

```sql
SELECT version();
SHOW TIMEZONE;
SELECT table_name, column_name, data_type, is_nullable
FROM information_schema.columns
WHERE table_schema = 'public'
  AND table_name IN ('users', 'orders_customers', 'payments',
                     'payment_details', 'order_package_rel')
ORDER BY table_name, ordinal_position;
```

Hasil schema tersanitasi dapat dijadikan dokumen terpisah beserta tanggal dan
environment. Jangan mengklaim query ini sudah dijalankan oleh audit paket.

## ANDIKA-502: Pencarian nama dan SQL proyek

`CustomerModel::getByName` memakai `LIKE '%...%'`, limit 5. Beberapa filter
order dan meeting juga memakai `like`. Semantik pencocokan case mengikuti
operator dan collation yang dipakai; untuk case-insensitive Postgres menyediakan
`ILIKE` sesuai locale. Mengubah operator harus menjadi keputusan behavior yang
diuji. `%` dan `_` tetap wildcard dalam pattern walaupun value di-bind dengan
aman; tentukan apakah pencarian pengguna perlu literal wildcard.
Rujukan: [pattern matching PostgreSQL 18](https://www.postgresql.org/docs/18/functions-matching.html).

`OrdersCustomersModel::getAll/getById` memakai `?::date` dan pengurangan date
untuk `days_remaining`. `MeetingScheduleModel` memakai `CURRENT_DATE` dan
`CURRENT_TIME` untuk status ongoing/done/upcoming, lalu memfilter collection.
Itu alasan test SQLite tidak cukup untuk memverifikasi query terkait.

`PaymentsModel::getAll` memuat agregat `SUM(amount)` dikelompokkan `payment_id`
pada relasi installment. Periksa hasil agregasi dan soft delete ketika angka
ringkasan berbeda dari daftar cicilan.

## ANDIKA-503: Uang, transaksi, dan concurrency

Migration amount cicilan memakai bigint. Jangan mengubahnya menjadi float
untuk rupiah. Periksa unit harga pada semua caller; belum ada spesifikasi mata
uang/rounding formal pada audit ini. `payment.amount` boleh null, sehingga
null perlu diperlakukan terpisah dari nol dalam perhitungan pelunasan.

Membaca total lalu menulis cicilan dapat race. Transaksi, row lock, dan constraint
unik menyelesaikan bagian berbeda dari masalah; rancang berdasarkan behavior
yang diminta dan test concurrent. Nomor order `count()+1` juga perlu diperiksa.
Jangan mengklaim sekadar menambah transaksi mencegah semua duplikasi.
Rujukan: [transaction isolation](https://www.postgresql.org/docs/18/transaction-iso.html).

## ANDIKA-504: Koneksi Docker dan performa

Dari backend dalam jaringan Compose, hostname service database adalah `db`;
dari host melalui port publish default `127.0.0.1:5432`. File `.env` aktual tidak
dibaca; jangan berasumsi koneksi sudah memakai alamat tersebut. Dump init di
Compose berjalan pada inisialisasi direktori data kosong, bukan setiap restart.

Cari bottleneck dengan jumlah query dan `EXPLAIN` pada SELECT yang tepat,
lalu tentukan index dari filter/join nyata. `EXPLAIN ANALYZE` mengeksekusi query;
gunakan hanya setelah memahami biaya dan environment. Jangan menambah index
untuk semua kolom atau memasang pgvector ke database aplikasi hanya untuk KB.
Rujukan: [EXPLAIN](https://www.postgresql.org/docs/18/using-explain.html),
[image resmi PostgreSQL](https://hub.docker.com/_/postgres).
