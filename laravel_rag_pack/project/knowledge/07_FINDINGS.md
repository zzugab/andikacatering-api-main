# ANDIKA-700: Temuan dan ketidakpastian yang harus diketahui model

Audit statis 2026-10-27. Tidak ada perbaikan aplikasi di paket knowledge ini.
Daftar ini membantu diagnosis, bukan instruksi melakukan refactor otomatis.

## ANDIKA-701: Macro response tidak mengikuti asumsi call site

Fakta: `AppServiceProvider::boot` mendefinisikan argumen kedua success sebagai
message dan HTTP selalu 200; controller sering memasukkan angka 200.
Dampak: model yang mengira argumen itu status HTTP akan memberi patch salah.
Periksa body dan HTTP terpisah; kontrak frontend belum tersedia.

## ANDIKA-702: Drift migration dan model

Fakta: create_users mendeklarasikan `role`, model memakai `role_id` dan
`is_active`; migration yang sesuai belum ditemukan. Model tabel
`payment_details` dan `order_package_rel` ada, migration pembuatnya belum
ditemukan. Jangan menyatakan database hidup pasti rusak; schema belum dibaca.

## ANDIKA-703: Route tidak selalu menjamin method ada

`POST /api/register` menunjuk `AuthController::register`, tetapi method itu
tidak ditemukan pada class snapshot ini. `GET /api/user` juga dideklarasikan
dua kali, satu closure dan satu controller. Verifikasi route runtime yang
terdaftar sebelum mengubah behavior atau menyatakan endpoint berfungsi.
Inventaris generated menampilkan deklarasi source; tidak membuktikan endpoint
berhasil dipanggil.

## ANDIKA-704: Status akhir acara

`OrderController::eventEnd` menulis `event_finish` pada cabang payment completed;
enum migration `orders_customers.status` tidak memuat nilai ini (memuat
`event_completed` dan `case_close` di antaranya). Periksa constraint database
dan keputusan bisnis status sebelum memilih pengganti.

## ANDIKA-705: Write berantai dan error tersembunyi

`storeWithCustomer` melakukan beberapa write tanpa transaksi menyeluruh;
banyak helper model menangkap Throwable dan return array. Transaksi di caller
tidak otomatis rollback jika kegagalan ditelan. Array gagal tetap truthy.
Ini titik diagnosis untuk data sebagian dan HTTP sukses yang menyesatkan.

## ANDIKA-706: Kredensial dalam source autentikasi

Fakta: konfigurasi SMTP pada `AuthController::sendForgotPassword` mengandung
credential literal. Nilainya sengaja tidak didokumentasikan atau diekspor oleh
generator. Jika masih berlaku, pemilik perlu merotasi credential dan
memindahkan konfigurasi ke environment/config. Jangan mengunggah body source
autentikasi mentah ke knowledge. Audit ini tidak memverifikasi validitasnya.

## ANDIKA-707: Hal yang belum diketahui

- Versi proses PHP/Laravel/PostgreSQL VM, schema dan constraint aktual.
  Pengguna mengonfirmasi Laravel 13/PHP 8.5.12; lokal terverifikasi framework
  13.38.0 pada lock/vendor, PHP CLI 8.5.12, Composer 2.10.3.
- Versi Ollama/Hermes/Open WebUI, quantization model terpasang, RAM VM,
  model GPU, pembagian CPU/GPU, num_ctx, dan latency aktual per fase.
- Kontrak frontend lengkap, aturan bisnis yang belum diekspresikan oleh kode,
  preferensi pribadi pengguna, dan cakupan test domain (hanya example ditemukan).
- Hasil benchmark model dengan knowledge ini: belum dijalankan pada VM.

Untuk hal di atas, jawaban yang benar menyebut apa yang perlu diperiksa;
tidak mengisi celah dengan tebakan yang ditulis sebagai fakta.
