# Penggunaan pada VM Ollama + Hermes + Open WebUI

Anda sudah menjalankan stack ini; tidak perlu instal ulang, mengganti container,
atau mengubah database aplikasi. Semua langkah konfigurasi di bawah adalah
petunjuk untuk VM, **belum dijalankan oleh audit repository ini**.

## 1. Pisahkan dua sasaran optimasi

Knowledge membantu relevansi, penelusuran source, dan pengakuan ketidakpastian.
Kecepatan token dipengaruhi model/quantization, offload, context, GPU/CPU/RAM,
dan beban lain. Baseline pengguna 10 token/detik perlu dibandingkan pada task
dan hardware yang sama; paket ini tidak menjanjikan peningkatan angka tersebut.

Tag resmi `deepseek-r1:14b` tercatat Q4_K_M dengan file sekitar 9,0 GB. Pada
GPU diskrit 8 GB, inferensi penuh di GPU tidak realistis untuk tag ini setelah
memperhitungkan cache dan overhead; CPU/RAM offload mungkin terjadi. Model
custom pengguna bisa berbeda, jadi periksa tag yang benar terlebih dahulu.
Storage 150 GB membantu menyimpan model, bukan menambah VRAM.
Sumber: [tag resmi Ollama](https://ollama.com/library/deepseek-r1:14b).

```bash
ollama --version
ollama list
ollama show deepseek-r1:14b
ollama ps
nvidia-smi
free -h
df -h
```

`nvidia-smi` untuk VM NVIDIA; periksa model saat aktif di `ollama ps`.
Kolom PROCESSOR menunjukkan pembagian CPU/GPU. Jangan menyimpulkan bottleneck
hanya dari token/detik atau ukuran disk. Nama yang diketik pengguna
`deepseekr1:14b` mungkin alias custom; nama resmi memakai `deepseek-r1:14b`.

## 2. Profil awal tanpa menimpa model lama

Periksa `FROM` di `ollama/Modelfile` dan ganti bila memakai alias custom.
Profil ini memilih context 4096, output maksimal 1536, temperature 0.6,
top_p 0.95. Angka context/output adalah titik awal pengujian, bukan hasil
optimasi hardware. Batas output juga dapat habis untuk reasoning R1 sehingga
jawaban final belum muncul; pecah task atau naikkan batas secara terukur.

```bash
ollama create andika-maintenance -f laravel_rag_pack/project/ollama/Modelfile
ollama show andika-maintenance --modelfile
```

DeepSeek merekomendasikan temperature 0.5–0.7 dan instruksi di user prompt.
Karena itu profil ini tidak menambah SYSTEM, TEMPLATE, atau stop token custom;
prompt proyek dikirim bersama pertanyaan. Ini bukan fine-tuning.
Sumber: [rekomendasi R1](https://huggingface.co/deepseek-ai/DeepSeek-R1),
[Modelfile Ollama](https://docs.ollama.com/modelfile).

Untuk memori, kandidat setting **server Ollama** (bukan `.env` Laravel):

```ini
OLLAMA_NUM_PARALLEL=1
OLLAMA_MAX_LOADED_MODELS=1
OLLAMA_FLASH_ATTENTION=1
OLLAMA_KV_CACHE_TYPE=q8_0
```

Pasang lewat mekanisme service/proses Ollama yang memang digunakan lalu restart
service sesuai prosedur VM. Backend/GPU perlu mendukung Flash Attention;
periksa log server. KV quantization mengurangi cache, bukan ukuran bobot model.
Jangan langsung memilih q4_0 karena akurasinya dapat berubah. Menurunkan
concurrency menghindari penggandaan beban konteks.
Sumber: [FAQ Ollama](https://docs.ollama.com/faq).

Uji 4096 dahulu. Jika konteks terlalu pendek dan resource cukup, bandingkan
8192 dengan query yang sama. Jangan memaksakan 32k/64k hanya untuk agent.
Target awal prompt sekitar 2000–2500 token dengan ruang output/reasoning dan
history. Batas karakter alat ini hanya perkiraan praktis; tokenizer model dan
prompt_eval_count dari API menjadi pemeriksaan sebenarnya. Mulai chat baru
saat history mulai memenuhi konteks.

## 3. Jalur paling ringan: retrieval offline, lalu chat

```bash
python3 laravel_rag_pack/project/tools/knowledge.py refresh
python3 laravel_rag_pack/project/tools/knowledge.py search 'success argumen kedua message status HTTP' --prompt > /tmp/andika-prompt.txt
ollama run andika-maintenance < /tmp/andika-prompt.txt
```

Anda juga bisa menempel isi prompt ke Open WebUI. Tidak ada embedding model
yang ikut memakai VRAM pada jalur ini. Setiap pertanyaan mengambil potongan
Markdown dan inventaris deklarasi; untuk patch lampirkan source method aktual
yang sudah disanitasi, bukan seluruh controller berukuran besar.

Pencarian lexical perlu identifier. Gunakan `storeWithCustomer`,
`PaymentInstallmentsModel`, `response success`, atau `role_id` bila pertanyaan
umum memberi hasil kurang tepat. `--top-k 4 --budget-chars 7500` membatasi
**seluruh output** termasuk brief/prompt. Turunkan bila UI menambah prompt atau
history besar; bila budget tak cukup untuk brief, tool menolak dan menjelaskan.

## 4. Open WebUI: buat KB proyek terpisah

1. Buat Knowledge `Andika Catering Maintenance` pada instance yang sudah ada.
2. Unggah semua `.md` dalam `project/knowledge/`, termasuk `generated/`.
   Jangan unggah seluruh repo, seeder, dump, `.env`, atau log.
3. Gunakan retrieval untuk dokumen panjang. Titik awal eksperimen: chunk
   800–1200 karakter, overlap 100–150, top-k 3–4. Sesuaikan dengan versi UI dan
   lihat passage yang benar-benar masuk prompt.
4. Gunakan embedding lokal yang sudah bekerja. Jangan mengganti embedding
   tanpa reindex seluruh KB. Jika memori ketat, indeks saat generation berhenti
   atau jalankan embedding pada CPU sesuai kemampuan backend.
5. Attach KB pada chat/preset. Untuk R1 yang tidak menjalankan tool knowledge
   secara andal, gunakan mode legacy bila tersedia, atau jalur paste prompt
   offline pada langkah 3. Jangan menganggap attachment membuktikan retrieval.
6. Kirim `prompts/maintenance.txt` sebagai instruksi user di awal task; untuk
   UI yang hanya menyediakan system field, anggap itu eksperimen kompatibilitas
   dan bandingkan dengan jalur user prompt. Hindari duplikasi brief panjang
   pada system, preset, dan attachment sekaligus.
7. Periksa passage/citation dan jalankan evaluasi, lalu reupload/reindex saat
   inventaris berubah.

Open WebUI native retrieval memerlukan model memanggil knowledge tools;
full-context dokumen pada chat bisa menjadi fallback untuk referensi pendek.
Jangan menaruh semua dokumen pada full context.
Sumber: [Knowledge Open WebUI](https://docs.openwebui.com/features/workspace/knowledge/),
[RAG Open WebUI](https://docs.openwebui.com/features/chat-conversations/rag/).

## 5. Hermes: berikan penelusuran yang terbatas

Knowledge Open WebUI tidak otomatis tersedia pada Hermes yang memanggil
Ollama melalui jalur lain. Tempatkan checkout proyek ini di workspace VM
Hermes. Jika session Hermes memiliki tool terminal, minta secara eksplisit:

```text
Bekerja dari root repository Andika Catering. Baca
laravel_rag_pack/project/knowledge/00_PROJECT_BRIEF.md dan
laravel_rag_pack/project/knowledge/01_NAVIGATION.md.
Jalankan python3 laravel_rag_pack/project/tools/knowledge.py search
'storeWithCustomer customer order payment'.
Lalu baca method source yang dirujuk untuk mendiagnosis masalah saya.
Jangan membaca .env, dump, seeder, log mentah, atau credential.
Jika tool tidak tersedia, beri daftar potongan file yang perlu saya berikan.
```

Mulai dengan task kecil dan baca-saja. Jika R1 mengarang hasil terminal atau
tidak menjalankan tool, pindah ke retrieval yang dijalankan operator, lalu
paste hasilnya. Knowledge tidak menambahkan kemampuan tool calling ke bobot
model. File `../hermes-skill/` yang lama tetap dapat menjadi referensi umum;
jangan menganggap pemasangannya menghubungkan KB Open WebUI atau otomatis
menjadikan konteks proyek ini tersedia. Command konfigurasi Hermes bergantung
versi terpasang; cek bantuan versi VM sebelum mengubah runtime.

## 6. Jika 14B tetap lambat atau sering salah

Pertahankan 14B sebagai baseline, lalu jika diperlukan bandingkan varian lebih
kecil pada pertanyaan dan knowledge yang identik. Ukur kualitas patch,
ketepatan referensi, dan waktu jawaban final; model lebih kecil tidak otomatis
lebih benar. Jangan menurunkan quantization secara agresif tanpa evaluasi.
RAM VM belum diketahui, sehingga angka performa offload tidak dapat dipastikan.
Model tidak perlu dilatih ulang untuk mulai memakai paket ini.
