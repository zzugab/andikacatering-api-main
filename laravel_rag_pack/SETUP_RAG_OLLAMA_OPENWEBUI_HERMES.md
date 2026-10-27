# Setup Laravel RAG untuk Ollama + Open WebUI + Hermes Agent

Revisi: 2026-10-27. Sistem contoh: Linux host, Ollama sudah terpasang di host, Open WebUI berjalan pada Docker, dan model kustom dibangun dengan Modelfile dari DeepSeek-R1:14B.

## 0. Kenali tiga komponen

1. **Ollama generation model**: model yang menulis jawaban/kode, misalnya model kustom pengguna. Model harus sudah muncul di `ollama list` pada instance Ollama yang akan dipakai.
2. **Embedding model**: model khusus mengubah potongan dokumen menjadi vektor. Gunakan `nomic-embed-text` untuk langkah awal. Ia tidak menggantikan generation model.
3. **Open WebUI Knowledge**: mengunggah dokumen, memproses, menyimpan indeks vektor dan mengambil passage yang cocok ketika ada pertanyaan.

Hermes Agent adalah runtime agent untuk tool terminal/file/skills. Hermes dapat memakai endpoint Ollama; ia bukan vector store dan upload KB di Open WebUI tidak secara otomatis menjadi knowledge Hermes pada jalur lain.

## 1. Siapkan Ollama lokal

```bash
ollama list
ollama pull nomic-embed-text
ollama list
curl -s http://127.0.0.1:11434/api/tags
```

Cek nama persis model kustom kamu, misalnya `laravel-custom:latest`; jangan menebaknya ketika mengisi Open WebUI.

Tes Ollama embedding menggunakan native API:

```bash
curl -s http://127.0.0.1:11434/api/embed \
  -H 'Content-Type: application/json' \
  -d '{"model":"nomic-embed-text","input":"Laravel FormRequest validation"}'
```

Respons seharusnya berisi array `embeddings`. Outputnya besar; tidak perlu dibaca penuh.

## 2. Tentukan instance Ollama yang dipakai Open WebUI

Jika sebelumnya menjalankan `ghcr.io/open-webui/open-webui:ollama`, image tersebut memasukkan Ollama di dalam container. Itu bisa merupakan **instance berbeda** dari Ollama host. Perintah `ollama list` di host dan `docker exec open-webui ollama list` dapat menghasilkan daftar berbeda. Hindari kebingungan: jadikan Ollama host sebagai satu-satunya backend model jika model kustom diciptakan di host.

Cek image dan konfigurasi saat ini:

```bash
docker inspect open-webui --format '{{.Config.Image}}'
docker inspect open-webui --format '{{range .Config.Env}}{{println .}}{{end}}' | grep -E 'OLLAMA|RAG_' || true
```

Jika Open WebUI saat ini sudah bisa terhubung ke Ollama host dan model kustomnya tampil, lanjut ke langkah 3 tanpa membuat ulang container.

**Linux Docker bridge:** alamat `localhost:11434` di dalam container menunjuk ke container, bukan host. `host.docker.internal` perlu host mapping `--add-host=host.docker.internal:host-gateway` atau `extra_hosts` pada Compose. Proses Ollama host harus mendengarkan di interface yang dapat dijangkau Docker, bukan hanya `127.0.0.1`. Periksa binding/firewall dengan hati-hati; jangan mengekspos Ollama port 11434 langsung ke internet.

Contoh konfigurasi host Ollama jika perlu akses dari jaringan Docker bridge dan menggunakan systemd:

```bash
sudo systemctl edit ollama
```

Tambahkan drop-in berikut:

```ini
[Service]
Environment="OLLAMA_HOST=0.0.0.0:11434"
```

Lalu:

```bash
sudo systemctl daemon-reload
sudo systemctl restart ollama
ss -lntp | grep 11434
```

**Keamanan:** binding `0.0.0.0` membuka listener pada semua interface. Batasi akses melalui firewall ke Docker bridge/host lokal saja. Jika host terpapar internet atau LAN tak tepercaya, gunakan desain jaringan alternatif yang membatasi listener dan jangan melakukan perubahan di atas tanpa mengamankan firewall.

Jika perlu membuat ulang Open WebUI agar memakai Ollama host, pastikan volume `open-webui` sudah dibackup dan tidak dihapus. Contoh container frontend `:main` (tanpa Ollama kedua):

```bash
docker stop open-webui
docker rm open-webui

docker run -d \
  --name open-webui \
  --restart always \
  -p 3000:8080 \
  --add-host=host.docker.internal:host-gateway \
  -e OLLAMA_BASE_URL=http://host.docker.internal:11434 \
  -e RAG_EMBEDDING_ENGINE=ollama \
  -e RAG_EMBEDDING_MODEL=nomic-embed-text \
  -e RAG_OLLAMA_BASE_URL=http://host.docker.internal:11434 \
  -v open-webui:/app/backend/data \
  ghcr.io/open-webui/open-webui:main
```

Perintah ini adalah **opsi migrasi** saja, bukan langkah wajib. Ia mengganti image lama tetapi mempertahankan volume data bernama `open-webui`; data Ollama lama pada volume `ollama` tidak dihapus dan tidak terpakai oleh image baru. Periksa kebijakan backup, port, konfigurasi environment, dan konfigurasi saved connections sebelum menjalankannya. `--gpus=all` tidak dibutuhkan oleh frontend `:main` bila inferensi dilakukan Ollama host. Environment RAG di atas mungkin kalah prioritas dari setting yang sebelumnya sudah tersimpan di database Open WebUI; verifikasi di Admin UI dan simpan perubahan di sana bila perlu.

Alternatif tanpa mengubah Docker: gunakan instance Ollama bawaan image `:ollama` untuk model + embedding, tetapi model hasil Modelfile host harus dibuat/diimpor ke instance tersebut terlebih dahulu; ini menghabiskan storage tambahan.

## 3. Atur embedding dan retrieval di Open WebUI

Buka `http://localhost:3000` -> **Admin Panel / Settings -> Documents** (label menu menyesuaikan versi):

- Embedding engine: **Ollama**.
- Embedding model: **nomic-embed-text**.
- Ollama base URL untuk embedding: `http://host.docker.internal:11434` bila Ollama berjalan di host melalui bridge.
- Text splitter: **Character** (default, titik awal).
- Chunk size: **1000** karakter (titik awal, bukan nilai universal).
- Chunk overlap: **150** karakter (titik awal).
- Top K: **3-5** bila context lokal terbatas.
- Hybrid search: uji setelah pipeline dasar bekerja, terutama bila pencarian identifier PHP tidak tepat.

Simpan. Jika sebelumnya ada dokumen yang diindeks menggunakan model embedding lain, **reindex** knowledge base setelah mengganti model embedding. Jangan campurkan embedding lama dan baru.

## 4. Unggah file ke knowledge base

1. Buka **Workspace -> Knowledge -> Create**.
2. Buat KB bernama `Laravel Engineering KB`.
3. Unggah `LARAVEL_RAG_KNOWLEDGE.md`, kemudian tunggu pemrosesan selesai.
4. Unggah `PERSONAL_CODING_STYLE_TEMPLATE.md` **setelah placeholder sudah disesuaikan** dengan project nyata; sebelum itu jangan perlakukan template sebagai gaya final.
5. Untuk dokumentasi framework yang lebih lengkap, tambahkan halaman atau dokumen Laravel resmi **sesuai major version project** per topik, bukan menjadikan dokumen ringkasan ini satu-satunya sumber.
6. Validasi tab **Indexed text**: pastikan isi Markdown benar-benar terdeteksi dan tidak kosong.

Dengan Native function calling, model harus memanggil tool Knowledge untuk memakai KB terlampir; pengetahuan tidak otomatis dikirim ke model. Model lokal kecil atau reasoning-only mungkin tidak memanggil tool secara andal. Jika demikian, coba **Legacy function calling** pada model preset, atau uji dengan memasang dokumen secara langsung pada chat. Mode **Full Context** cocok untuk style guide pendek, tetapi bukan untuk handbook besar karena memenuhi context window.

## 5. Buat model preset di Open WebUI

1. Buka **Workspace -> Models -> New/Create** (nama menu bisa bervariasi).
2. Gunakan model Ollama custom milikmu sebagai **base model**.
3. Isi nama, misalnya `Laravel Developer RAG`.
4. Tempel isi `OPENWEBUI_SYSTEM_PROMPT.txt` di **System Prompt**.
5. Attach **Laravel Engineering KB** di bagian Knowledge.
6. Simpan; mulai chat baru dengan model preset ini.

Tes:

- `Cari pedoman KB-011. Apakah response()->success() fungsi bawaan Laravel? Jelaskan bagaimana memverifikasinya.`
- `Menurut knowledge yang kamu gunakan, kapan memakai with() vs load() pada Eloquent? Sebutkan sumbernya.`
- `Tuliskan controller POST contoh dengan validasi FormRequest, dan beritahu bagian yang masih asumsi.`

Periksa citations/tool calls atau retrieved passages. Kalau jawabannya benar tetapi model tak menggunakan KB, pengujian RAG belum terkonfirmasi.

## 6. Perhatikan context length model Ollama

Model lokal dengan context window kecil akan memotong prompt dan potongan RAG. Periksa pengaturan `num_ctx` pada Modelfile:

```bash
ollama show NAMA_MODEL_CUSTOM --modelfile
```

Jika VRAM hanya 8 GB dan model dasar 14B, tingkatkan `num_ctx` bertahap sambil mengamati penggunaan memori. Jangan langsung memasang context sangat panjang karena kebutuhan KV cache dapat menyebabkan offload CPU/kehabisan memori. Hindari nilai `num_ctx` yang lebih besar daripada dukungan arsitektur model.

Contoh parameter **hanya untuk model baru setelah pengujian resource**:

```modelfile
FROM NAMA_MODEL_CUSTOM
PARAMETER num_ctx 8192
```

Simpan misalnya sebagai `Modelfile.context`, kemudian jalankan `ollama create laravel-custom-8k -f Modelfile.context` jika resource cukup. Pengubahan ini opsional dan **bukan syarat memasang RAG**.

## 7. Posisi Hermes Agent: dua jalur yang berbeda

### Jalur A (direkomendasikan dulu): RAG di Open WebUI -> Ollama langsung

Ini jalur paling sederhana untuk menguji bahwa model kustom memakai sumber Laravel. Hermes tidak diperlukan untuk retrieval normal.

### Jalur B: Hermes Agent sebagai coding agent

Hubungkan Hermes ke Ollama melalui `hermes model` -> Custom endpoint, URL `http://localhost:11434/v1`, model = **nama persis model kustom pada Ollama host**. Hermes memerlukan model yang sanggup tool calling dan context length besar; dokumentasi Hermes memperingatkan bahwa kebutuhan minimum agent adalah sekitar **64.000 token context**. Pada GPU kelas 8 GB dengan model 14B, konfigurasi ini bisa sangat berat/tidak praktis. Jangan mengklaim Hermes akan bekerja stabil pada model tersebut tanpa uji nyata.

Hermes memiliki sistem **Skills** untuk memuat instruksi dan file referensi. Paket ini menyediakan folder `hermes-skill/laravel-coding/` dengan `SKILL.md` dan `references/`. Instal secara lokal dengan:

```bash
mkdir -p ~/.hermes/skills/development
cp -a ./hermes-skill/laravel-coding ~/.hermes/skills/development/
```

Jalankan dari direktori hasil ekstraksi ZIP (yang berisi `hermes-skill/`). Mulai sesi Hermes baru agar skill terdeteksi. Contoh:

```bash
hermes skills list
hermes chat -q '/laravel-coding Jelaskan FormRequest dan cek apakah response macro saya sudah terverifikasi'
```

**Penting:** Skill yang membaca Markdown secara on-demand ini **bukan vector RAG**. Agar Hermes dapat melakukan pencarian vektor terhadap knowledge Open WebUI dari CLI/gateway lain secara mandiri, perlu tambahan tool/MCP/API retrieval yang ditulis dan dikonfigurasi secara khusus. Jangan berasumsi skill atau upload KB otomatis menyalurkan seluruh dokumen dari WebUI ke Hermes.

### Jika ingin Open WebUI berbicara ke Hermes gateway

Konfigurasikan Hermes API server dengan credential milikmu:

```bash
hermes config set API_SERVER_ENABLED true
hermes config set API_SERVER_KEY GANTI_DENGAN_SECRET_KUAT
hermes gateway
```

Bila Hermes gateway di host, Open WebUI Docker menambahkan koneksi **OpenAI-compatible** menggunakan `http://host.docker.internal:8642/v1`, key yang cocok. Host mapping Docker tetap diperlukan. Server Hermes default bind localhost; untuk Docker bridge, gateway perlu interface yang dapat dijangkau container dan harus dibatasi firewall. Alternatif `--network=host` mengubah port/layout Docker. Mode ini berbeda dari preset Ollama langsung, dan retrieval KB Open WebUI melalui Hermes perlu diuji, bukan diasumsikan otomatis.

## 8. Cara membuat coding style benar-benar personal

Gunakan Codex di root repository untuk menghasilkan `PERSONAL_CODING_STYLE_TEMPLATE.md` versi final. Prompt:

> Audit source code Laravel project secara read-only. Pahami framework version, controllers, FormRequests, services, models, API resources, response macros, exceptions, route naming, tests, database, dan formatting. Abaikan vendor, node_modules, .git, generated files, .env, secrets, credentials, serta data pribadi. Dari contoh yang **benar-benar ada**, isi PERSONAL_CODING_STYLE_TEMPLATE.md dengan aturan nyata berikut path sumber dan snippet pendek yang disanitasi. Bedakan aturan yang konsisten, pengecualian, dan asumsi. Jangan mengarang. Berikan draft dan laporkan temuan sebelum menimpa dokumen. Jangan mengubah aplikasi, migrations, atau data.

Setelah direview, upload/reindex style guide tersebut. Tujuan bukan melatih ulang bobot DeepSeek; tujuannya membuat jawaban mengikuti konvensi project.

## 9. Troubleshooting singkat

- **Model Ollama tidak muncul**: pastikan instance benar; cek host `ollama list`, URL di Open WebUI Connections, port 11434 dari container dan `host.docker.internal`.
- **Ollama Network Problem**: periksa apakah instance host hanya bind loopback; periksa firewall, host-gateway mapping, dan `docker logs open-webui`.
- **Embedding gagal**: pull `nomic-embed-text` di instance yang sama dengan URL RAG; periksa API `/api/embed`, setting RAG Engine, dan reindex.
- **Knowledge tidak dipakai**: pastikan model/pesan attach KB, selesai indexing, indexed text ada; pada Native mode model harus memanggil tool knowledge.
- **Response lambat/OOM**: turunkan `num_ctx`, Top K, jumlah retrieved docs atau ukuran model; jangan menambah embedding dan generation workload bersamaan tanpa memantau resource.
- **Source Laravel salah versi**: pisahkan KB per mayor Laravel, pilih salah satu yang sesuai `composer.lock`.
- **Kode tidak mirip style pribadi**: isi template dari repository, jangan menganggap sample global Laravel adalah preferensi pengguna.

## 10. Rujukan resmi

- Open WebUI Knowledge: https://docs.openwebui.com/features/workspace/knowledge/
- Open WebUI RAG: https://docs.openwebui.com/features/chat-conversations/rag/
- Open WebUI RAG troubleshooting: https://docs.openwebui.com/troubleshooting/rag/
- Open WebUI environment vars: https://docs.openwebui.com/reference/env-configuration/
- Open WebUI Hermes integration: https://docs.openwebui.com/getting-started/quick-start/connect-an-agent/hermes-agent/
- Hermes providers: https://hermes-agent.nousresearch.com/docs/integrations/providers
- Hermes skills: https://hermes-agent.nousresearch.com/docs/guides/work-with-skills
- Laravel releases: https://laravel.com/docs/releases
- Ollama API embed: https://docs.ollama.com/api/embed
