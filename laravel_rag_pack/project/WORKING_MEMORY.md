# Memori kerja yang dapat dikurasi

## Konteks yang disampaikan pengguna

- Proyek yang dipelihara: backend Andika Catering di repository ini.
- Model lokal DeepSeek-R1 14B pada remote VM, GPU VRAM 8 GB, storage 150 GB.
- Ollama, Hermes, dan Open WebUI sudah digunakan.
- Baseline chat sekitar 10 token/detik; masalah utama jawaban berhalusinasi.
- Tujuan: knowledge tentang cara bekerja dan source proyek untuk maintenance.
- Koreksi pengguna 2026-10-27: sudah upgrade ke Laravel 13, PHP 8.5.12,
  Composer dan dependency lain setelah masalah PHP sebelumnya. Detail bug
  belum diberikan. Lock/vendor lokal: Laravel 13.38.0; CLI Composer 2.10.3.

## Preferensi yang belum dikonfirmasi

Belum ada bukti cukup tentang format patch favorit, strategi branch/commit,
seberapa jauh refactor diizinkan, urutan debug pribadi, atau kontrak frontend
yang harus selalu dijaga. Pola source teramati ada di knowledge/02; pola itu
bukan persetujuan pengguna atas semua praktiknya.

## Cara mencatat pembelajaran berikutnya

Setelah pengguna mengonfirmasi sebuah keputusan, catat tanggal, situasi,
keputusan, scope, dan source yang mendukung. Contoh format kosong:

```text
Tanggal:
Situasi:
Keputusan yang dikonfirmasi:
Berlaku untuk:
Referensi file/method/test:
Menggantikan keputusan lama:
```

Catat hasil verifikasi final, bukan transcript panjang, dugaan, chain of
thought model, token, password, atau data customer. Knowledge tidak berubah
otomatis dari percakapan Open WebUI/Hermes; update file dan reindex dengan
sengaja agar keputusan yang salah tidak menjadi memori permanen.
