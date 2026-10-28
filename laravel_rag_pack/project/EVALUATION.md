# Evaluasi knowledge dan model

Ini benchmark manual yang dapat diulang di VM, bukan klaim model sudah lulus.
Catat tag+digest/quantization model, versi runtime, RAM/GPU, num_ctx/output,
parameter sampling, dan apakah passage benar-benar diambil. Bandingkan baseline
tanpa knowledge dengan knowledge pada **model dan prompt task yang sama**.

## Pertanyaan dan jawaban acuan

| No | Pertanyaan | Fakta minimum yang harus muncul |
| --- | --- | --- |
| 1 | Apa versi Laravel proyek ini dan sumbernya? | Lock/vendor lokal 13.38.0, constraint ^13.30; PHP CLI 8.5.12, Composer 2.10.3; runtime VM belum dicek |
| 2 | Apakah success($data, 201) mengirim HTTP 201? | Tidak; message=201, HTTP 200; AppServiceProvider::boot |
| 3 | Mengapa if($result) bisa salah pada hasil UserModel? | Array nonkosong truthy meski status false; periksa status/data |
| 4 | Apakah find($uuid) benar untuk semua model? | Tidak otomatis; primary key dan UUID perlu dicek |
| 5 | Telusuri POST /api/order. | storeWithCustomer, customer → order → detail → payment; role/auth/status |
| 5a | Apakah `storeWithCustomer` memanggil `CustomerModel::getByName()`? | Tidak terlihat; method memakai query inline pada controller. `getByName()` adalah method terpisah untuk pencarian customer |
| 5b | Apa yang dibuat `OrdersCustomersModel::store()`? | Record order dan `order_name`; customer dibuat/ditemukan lebih dulu oleh controller |
| 5c | Bagaimana ejaan role pada route order? | `Superadmin`, `Admin`, `Akuntan`; jangan menurunkan case identifier |
| 6 | Mengapa menambah DB::transaction belum tentu rollback kegagalan model? | Throwable dapat ditangkap lalu return array; closure tidak melempar |
| 7 | Di mana mencari PDF invoice dan helper tanggal? | FinanceController::invoicePrint, invoice-print-layout, DateHelper |
| 8 | Mengapa landing masih menampilkan data lama? | Cache landing_page_data_*, TTL 300; belum tentu satu-satunya sebab |
| 9 | Apakah migration users sama dengan model? | role vs role_id/is_active; schema hidup belum diperiksa |
| 10 | Apakah POST /register pasti dapat digunakan? | Route ada, method register tidak ditemukan pada AuthController snapshot |
| 11 | Apa masalah potensial status akhir acara lunas? | eventEnd menulis event_finish, tidak ada pada enum migration |
| 12 | Jelaskan delete cicilan dan efeknya ke pelunasan. | destroyInstallment, sumByPayment, saldo, tipe terakhir, status payment |
| 13 | Sebutkan Redis queue/Horizon yang dipakai proyek. | Tidak ada bukti dipakai; jangan mengarang dari fitur Laravel umum |
| 14 | Berapa customer aktif dan index yang terpasang di database VM? | Tidak diketahui tanpa query/schema; dilarang mengarang angka/index |
| 15 | Apakah Anda sudah menjalankan test patch ini? | Hanya mengatakan ya bila benar-benar ada eksekusi dan hasil |
| 16 | Apakah Open WebUI KB otomatis dipakai Hermes? | Tidak; jalur retrieval terpisah, perlu konteks/tool yang nyata |

Jangan masukkan file evaluasi ini ke KB utama saat mengukur kemampuan retrieval;
acuan jawaban harus terpisah agar model tidak sekadar mengambil tabel ini.

## Penilaian

Untuk setiap pertanyaan: fakta benar (0/1), referensi path/method tepat (0/1),
dan menghindari klaim tanpa bukti (0/1). Total 48. Tandai setiap fabrikasi
sebagai kegagalan penting meskipun skor keseluruhan naik. Jalankan 2–3 kali
karena sampling R1 dapat berbeda; review patch dengan lint/test terarah pada
checkout dan database testing yang sesuai sebelum dipakai.

Ukur retrieval terpisah: passage jawaban harus muncul pada hasil `search`
atau citations Open WebUI. Bila passage tidak terambil, perbaiki query/heading/
chunking terlebih dahulu. Bila passage benar tetapi jawaban salah, periksa
truncation prompt, kemampuan model, dan instruksi sebelum menambah dokumen.

## Catatan hasil VM

| Run | Model/digest | Knowledge | num_ctx | Input tokens | Output tokens | Token/detik | Waktu final | Skor /48 | Fabrikasi |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Baseline pengguna | DeepSeek-R1 14B; detail belum dicatat | Belum terverifikasi | Belum diketahui | — | — | ~10 | Belum dicatat | Belum dinilai | Dilaporkan ada |

Pada Ollama API, jika statistik tersedia: `eval_count / (eval_duration / 1e9)`
untuk laju generation. Catat pula `load_duration`, `prompt_eval_duration`, dan
`total_duration`; token/detik saja tidak menggambarkan waktu membaca konteks
dan waktu sampai jawaban akhir. Output thinking dapat terpisah dari content.
Sumber: [Ollama chat API](https://docs.ollama.com/api/chat).
