# Knowledge maintenance Andika Catering

Dibuat dari repository pada 27 Oktober 2026. Target: VM GPU VRAM 8 GB,
storage 150 GB, Ollama + Hermes + Open WebUI, DeepSeek-R1 14B; baseline
pengguna sekitar 10 token/detik dan jawaban masih berhalusinasi.

Paket ini menambahkan konteks proyek ke model saat bertanya. Bobot model tidak
berubah. Isinya merupakan hasil inspeksi kode, bukan rekaman riwayat kerja atau
preferensi pribadi yang sudah dikonfirmasi. Kode yang baru berubah selalu lebih
otoritatif daripada ringkasan ini.

## Mulai dari sini

1. Baca `knowledge/00_PROJECT_BRIEF.md` sebagai konteks pendek.
2. Ikuti `knowledge/01_NAVIGATION.md` untuk alur penelusuran.
3. Baca `LOCAL_MODEL_SETUP.md` untuk memasang knowledge pada stack VM yang ada.
4. Pakai `prompts/maintenance.txt` saat meminta analisis atau patch.
5. Jalankan evaluasi di `EVALUATION.md` sebelum membandingkan hasil.

Semua command berikut dijalankan dari **root repository** pada VM yang memiliki
checkout proyek ini. Python 3.9+ cukup; tidak perlu pip, embedding, database,
akses internet, atau GPU tambahan untuk alat pencarian ini.

```bash
python3 laravel_rag_pack/project/tools/knowledge.py refresh
python3 laravel_rag_pack/project/tools/knowledge.py search 'response success parameter message'
python3 laravel_rag_pack/project/tools/knowledge.py search 'alur membuat order customer pembayaran' --prompt
```

Command terakhir menghasilkan prompt yang dapat ditempel ke chat Open WebUI,
Ollama, atau Hermes. Pencarian ini berbasis kata kunci BM25, **bukan vector RAG**.
Ia hanya membaca Markdown kurasi dan inventaris deklarasi; tidak mengambil body
source code. Lampirkan potongan source terkait yang telah diperiksa bila meminta
patch. Alat ini tidak memanggil model atau menerapkan patch.

Periksa alat secara offline:

```bash
PYTHONDONTWRITEBYTECODE=1 python3 -m unittest discover -s laravel_rag_pack/project/tools -p 'test_*.py' -v
```

## Isi knowledge

| File | Dipakai untuk |
| --- | --- |
| `knowledge/00_PROJECT_BRIEF.md` | Stack, aturan minimum, sumber kebenaran |
| `knowledge/01_NAVIGATION.md` | Menelusuri gejala sampai route, model, dan test |
| `knowledge/02_CODE_CONVENTIONS.md` | Pola kode yang benar-benar terlihat |
| `knowledge/03_DOMAIN_FLOWS.md` | Order, customer, paket, cicilan, logistik, PDF |
| `knowledge/04_PHP_LARAVEL.md` | PHP 8.5.12/Laravel 13, struktur hasil upgrade |
| `knowledge/05_POSTGRES.md` | UUID, schema drift, SQL, transaksi, pengujian |
| `knowledge/06_RUNTIME.md` | Docker, Octane, cache, upload, pengoperasian |
| `knowledge/07_FINDINGS.md` | Ketidaksesuaian terverifikasi dan batas pengetahuan |
| `knowledge/generated/CONTROLLERS.md` | Seluruh deklarasi method controller |
| `knowledge/generated/MODELS.md` | Tabel, fillable, method, deklarasi relasi model |
| `knowledge/generated/MIGRATIONS.md` | Deklarasi schema dari migration |
| `knowledge/generated/ROUTES.md` | Deklarasi route dan batas grup middleware |
| `knowledge/generated/STACK.md` | Constraint dan versi dependency dari Composer |

Inventaris dibuat statis, tidak mem-boot Laravel. Nomor baris dan checksum
merujuk source saat `refresh`. Parser ringan ini bukan parser PHP penuh dan
bukan bukti schema database atau route runtime. Bila hasil meragukan, baca
file aslinya. `refresh` memperbarui inventaris saja; ringkasan kurasi tetap
perlu ditinjau saat behavior berubah.

Koreksi stack 2026-10-27: Laravel 13.38.0 terverifikasi pada lock/vendor lokal,
PHP CLI 8.5.12 dan Composer 2.10.3. Gunakan knowledge hasil revisi ini dan
hapus/ganti dokumen versi sebelumnya di KB agar informasi versi tidak bersaing.

## Memelihara knowledge

Setelah mengubah route/model/migration/controller, jalankan `refresh`, periksa
diff inventaris dan revisi bab domain yang terdampak. Untuk Open WebUI, unggah
ulang/reindex dokumen yang berubah. Chat lama mungkin tetap memegang konteks
lama; mulai chat baru setelah pembaruan penting.

Catat preferensi yang benar-benar Anda setujui di `WORKING_MEMORY.md`. Jangan
menganggap bug atau kebiasaan yang kebetulan terlihat sebagai aturan wajib.
Folder `../` masih memuat handbook Laravel umum dan template lama. Untuk KB
proyek ini, utamakan `knowledge/` di sini agar placeholder dan contoh Laravel
versi lain tidak ikut menjadi aturan proyek.

## Cakupan dan privasi

Tidak membaca `.env`, dump database, log, upload, collection Postman, atau isi
seeder ke knowledge. Generator membaca file PHP aplikasi untuk mengambil
deklarasi terbatas, bukan mengekspor body method. Source autentikasi mengandung
kredensial hardcoded; jangan unggah seluruh repository mentah ke KB.
Inventaris tetap perlu direview sebelum dibagikan: nama internal dan signature
juga merupakan informasi proyek. Tidak ada koneksi VM atau perubahan konfigurasi
Ollama/Open WebUI/Hermes yang dilakukan otomatis oleh paket ini.
