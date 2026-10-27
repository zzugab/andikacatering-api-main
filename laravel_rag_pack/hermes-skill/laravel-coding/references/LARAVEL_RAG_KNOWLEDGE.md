# Laravel Engineering Knowledge Base dan Coding Style

> Dokumen RAG kurasi untuk asisten coding lokal berbasis Ollama. Bahasa penjelasan: Indonesia. Bahasa identifier kode: Inggris.
>
> Revisi: 2026-10-27. Cakupan: praktik Laravel modern, API backend, Eloquent, testing, keamanan, deployment, dan panduan coding style yang **masih harus divalidasi terhadap repository pengguna**.
>
> Penting: Ini BUKAN salinan lengkap dokumentasi resmi Laravel dan BUKAN hasil audit repository. Setiap perilaku yang sensitif terhadap versi harus diverifikasi pada source code, `composer.lock`, dan dokumentasi versi yang sesuai.

## KB-001: Prinsip prioritas sumber dan kebijakan menjawab

Urutan acuan untuk keputusan implementasi:
1. Instruksi eksplisit pengguna dan kriteria fitur yang diminta, sepanjang aman.
2. Kode, test, kontrak API, dan konvensi yang nyata pada repository yang sedang dikerjakan.
3. Panduan coding style yang **sudah dikonfirmasi** pengguna di dokumen ini.
4. Dokumentasi resmi Laravel yang **sesuai versi project**.
5. Praktik umum PHP dan Laravel yang relevan.

Jangan mengarang nama class, endpoint, kolom tabel, relasi, response macro, environment variable, atau dependency. Jika tidak terlihat pada project, tandai sebagai contoh atau asumsi. Jika dokumentasi versi berbeda dari project, ikuti versi project dan jelaskan perbedaannya. Sebelum memberi patch, sebutkan file target, dampak terhadap route, database, tests, dan kompatibilitas bila relevan.

Untuk pertanyaan kode: beri penjelasan singkat, contoh yang bisa diterapkan, dependency atau import yang benar, petunjuk pengujian, serta peringatan jika menyentuh autentikasi, data pribadi, migration, atau deployment. Jangan mengklaim kode sudah dieksekusi atau lolos test kecuali benar-benar dieksekusi.

## KB-002: Metadata project yang perlu diisi

- Nama project: `ISI_NAMA_PROJECT`
- PHP version yang digunakan: `CEK_DARI_php_-v_DAN_composer.json`
- Laravel major version: `CEK_DARI_composer.lock_ATAU_php_artisan_--version`
- Database: `CEK_KONFIGURASI` (contoh: PostgreSQL, MySQL)
- Authorization: `CEK_POLICY_ATAU_GATE`
- Authentication API: `CEK_AUTH_SANCTUM_PASSPORT_ATAU_CUSTOM`
- Folder Service/Repository/Action: `CEK_STRUKTUR_NYATA`
- Testing: `CEK_PHPUNIT_ATAU_PEST`
- API response formatter: `CEK_MACRO_CUSTOM_ATAU_API_RESOURCE`
- Deployment: `CEK_DOCKER_COMPOSE_NGINX_CI_CD`

Cek versi tanpa mengubah aplikasi:
```bash
php artisan --version
php -v
composer show laravel/framework
cat composer.json
```
Jangan membaca atau menyebarkan isi `.env` atau secret. Simpulkan jenis environment variable dari `.env.example` jika tersedia.

## KB-003: Sumber dokumentasi resmi dan matriks versi

- Laravel 13: https://laravel.com/docs/13.x
- Laravel 12: https://laravel.com/docs/12.x
- Laravel 11: https://laravel.com/docs/11.x
- Laravel release notes: https://laravel.com/docs/releases
- Laravel API reference: https://api.laravel.com/
- Laravel Pint: https://laravel.com/docs/13.x/pint
- Laravel testing: https://laravel.com/docs/13.x/testing

Laravel 13 rilis 17 Maret 2026, membutuhkan PHP minimal 8.3; tidak berarti seluruh project sudah memakai 13. Gunakan bagian `/12.x/` atau `/11.x/` bila project menggunakan major tersebut. API baru Laravel 13, termasuk fasilitas AI/vektor, tidak boleh diasumsikan tersedia pada versi sebelumnya.

## KB-004: Peta topik dokumentasi Laravel

Pertimbangkan seluruh area berikut ketika mencari solusi. Cari bab spesifik yang sesuai versi saat pengguna meminta detail API atau signature method:

- Dasar: installation, configuration, directory structure, request lifecycle, routing, middleware, controllers, requests, responses, validation, error handling, logging.
- Arsitektur: service container, dependency injection, service providers, facades, contracts, events, listeners.
- Database: migrations, seeders, factories, query builder, transaction, pagination, Redis, connection configuration.
- Eloquent: models, attributes, casts, scopes, relationships, eager loading, API resources, serialization, soft deletes.
- Keamanan: authentication, authorization, policies, gates, CSRF, hashing, encryption, password reset, rate limiting.
- Background: queues, jobs, batches, retries, task scheduling, notifications, mail, events, broadcasting.
- Integrasi: HTTP client, filesystem/storage, caching, collections, localization, packages.
- Kualitas: unit tests, feature tests, HTTP tests, mocking, database tests, Pint, static analysis.
- Deployment: environment configuration, queues/workers, scheduler, config/routes/views caching, observability, Docker.
- Ekosistem opsional: Sanctum, Passport, Horizon, Telescope, Pulse, Octane, Scout, Reverb, Socialite, Laravel AI SDK/MCP pada versi yang mendukung.

Peta topik ini membantu retrieval. Ini tidak menggantikan teks dokumentasi API yang lengkap.

## KB-010: Default coding style (proposal, bukan preferensi pribadi yang terverifikasi)

- Ikuti pola kode yang **sudah dipakai project**. Jangan refactor folder hanya untuk memaksakan arsitektur baru.
- Format PHP sesuai Laravel Pint atau aturan formatter yang sudah ada. Gunakan import `use` yang eksplisit dan rapikan unused import.
- Nama class: PascalCase (`OrderController`, `StoreOrderRequest`); method/variable: camelCase (`createOrder`, `$orderItems`); tabel: snake_case plural (`order_items`); field: snake_case (`created_at`).
- Gunakan tipe parameter/return jika konsisten dengan project dan versi PHP; jangan memaksakan `declare(strict_types=1)` jika tidak digunakan.
- Gunakan `FormRequest` untuk validasi kompleks; `Policy`/Gate untuk otorisasi; `Resource` untuk representasi API ketika cocok.
- Controller idealnya mengoordinasikan HTTP, bukan memuat seluruh aturan bisnis. Pindahkan logika bisnis kompleks ke Service/Action **hanya jika cocok dengan pola repository**.
- Utamakan dependency injection; jangan menambahkan repository pattern tanpa kebutuhan nyata.
- Gunakan query Eloquent yang mudah dibaca, eager-load relasi untuk mencegah N+1, dan pagination untuk koleksi besar.
- Sertakan tests untuk perubahan behavior, terutama auth, validasi, dan transaksi.
- Jangan menambahkan dependency Composer tanpa alasan dan persetujuan.
- Pesan API harus konsisten dengan kontrak yang sudah ada; jangan mengganti field response sepihak.
- Kode contoh di knowledge base ini bersifat referensi, bukan instruksi untuk menyalin bulat-bulat ke semua project.

### Gaya respons asisten saat menulis kode

1. Identifikasi file yang harus dibuat atau diubah.
2. Jelaskan implementasi dan alasan singkat.
3. Tampilkan patch atau file lengkap sesuai permintaan pengguna.
4. Tunjukkan command test/lint yang tepat.
5. Sebutkan asumsi, dependency, dan kompatibilitas versi.

## KB-011: Gaya API response project dan response macro

Dari contoh sintaks yang pernah digunakan pengguna, mungkin ada pola berikut:
```php
return response()->success($data, 200);
return response()->failed('Something error', 400);
```

**Status: PERLU KONFIRMASI DI REPOSITORY.** `success()` dan `failed()` BUKAN method standar `Illuminate\Contracts\Routing\ResponseFactory`. Biasanya keduanya dibuat melalui `Response::macro()` atau helper custom. Periksa service provider atau file bootstrap dan signature macro sebelum meniru; jangan mengarang bentuk JSON-nya. Jika project memang menggunakannya, pertahankan bentuk response dan kode status yang konsisten. Jika macro tidak ada, gunakan `response()->json(...)` atau API Resource seperti kontrak project.

Contoh standar Laravel ketika *tidak* menggunakan macro custom:
```php
return response()->json([
    'message' => 'Order created',
    'data' => $order,
], 201);
```

Berhati-hati: status 201 untuk resource baru; 200 untuk berhasil biasa; 204 tanpa body untuk operasi yang memang tidak mengembalikan konten; 401 tanpa autentikasi; 403 tidak berizin; 404 tidak ditemukan; 422 validasi gagal. Jangan memaksa semua error menjadi 400.

## KB-020: Request lifecycle, routing, middleware

Alur umum HTTP: web server -> public/index.php -> bootstrap aplikasi -> HTTP middleware -> route resolution -> controller/callable -> service/model -> response -> middleware keluar. Detail bootstrap/middleware registration berbeda antar mayor Laravel; periksa versi dan struktur project.

Contoh route API (hanya bila routes/api.php digunakan):
```php
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
});
```

Syarat: Sanctum terpasang dan auth dikonfigurasi. `routes/api.php` tidak selalu ada pada instalasi baru; jangan otomatis berasumsi. Hindari menaruh rahasia atau verifikasi authorization hanya di frontend. Berikan nama route jika project memakai named route. Pilih `apiResource` hanya untuk endpoint yang benar-benar berkontrak resource REST.

## KB-021: Dependency injection dan service container

Inject dependency melalui constructor atau method secara jelas. Interface binding bisa didefinisikan melalui service provider ketika banyak implementasi atau kebutuhan test; concrete service sederhana sering bisa di-resolve otomatis.

```php
namespace App\Services;

use App\Models\Order;

class OrderService
{
    public function find(int $id): Order
    {
        return Order::query()->findOrFail($id);
    }
}
```

Pada controller:
```php
public function __construct(private readonly OrderService $orders)
{
}
```

Jangan menambahkan layer Service hanya demi pola jika method hanya berisi satu query sederhana dan project memilih pola controller langsung.

## KB-022: Request validation dan authorization

Gunakan FormRequest untuk validasi reusable dan policy/gate untuk otorisasi object-level. Perhatikan validasi `exists`, `unique`, `nullable`, `sometimes`, dan foreign key. Jangan percaya ID user/tenant yang dikirim klien tanpa pengecekan kepemilikan.

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
```

Contoh validasi tidak memverifikasi stok, harga, atau keanggotaan tenant. Aturan bisnis itu harus ditangani pada layer yang sesuai dan diuji. Untuk operasi atas resource yang telah ada, tetap panggil policy `authorize()`/`Gate` sesuai pola project.

## KB-023: Eloquent ORM, relationship, N+1

- Gunakan `with()` atau `load()` ketika membaca relasi berkali-kali.
- Gunakan `withCount()` untuk jumlah relasi tanpa memuat seluruh child.
- Gunakan `select()` seperlunya, tetapi sertakan primary key dan foreign key yang dibutuhkan eager loading.
- Gunakan `paginate()` untuk response berhalaman, jangan memanggil `get()` untuk jutaan record.
- `findOrFail()` menghasilkan 404 ketika model tak ditemukan dalam flow HTTP standar.
- Definisikan `$fillable` atau `$guarded` secara aman; jangan mass assign `$request->all()` tanpa validasi.
- `SoftDeletes` memerlukan kolom `deleted_at` dan pertimbangan unique constraints.

```php
$orders = Order::query()
    ->with(['user:id,name', 'items.product:id,name'])
    ->where('status', 'paid')
    ->latest('id')
    ->paginate(20);
```

Pastikan nama relationship dan skema sesuai repository. Contoh ini bersifat ilustrasi.

## KB-024: Query builder dan database transactions

Untuk operasi beberapa tabel yang harus atomik, gunakan `DB::transaction()`. Jangan menjalankan side-effect eksternal yang tidak dapat rollback di dalam transaksi tanpa strategi outbox/after-commit. Cegah race condition menggunakan unique constraint, conditional update, atau locking yang sesuai.

```php
use Illuminate\Support\Facades\DB;

$result = DB::transaction(function () use ($validated) {
    $order = Order::query()->create([
        'user_id' => auth()->id(),
        'notes' => $validated['notes'] ?? null,
        'status' => 'pending',
    ]);

    foreach ($validated['items'] as $item) {
        $order->items()->create([
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
        ]);
    }

    return $order->load('items');
});
```

Ini belum lengkap untuk sistem pembayaran/produksi: wajib validasi produk, kepemilikan, harga dari database, total, stok, dan concurrency sebelum digunakan. `auth()->id()` harus berasal dari middleware autentikasi yang benar.

## KB-025: API Resources, serialization dan pagination

Resource digunakan agar JSON response tidak bergantung langsung pada semua field model. Hindari mengekspos password, token, secret, internal note, atau data lintas tenant. Gunakan `whenLoaded()` untuk relasi opsional agar tidak memicu query tambahan.

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
```

`OrderItemResource` dalam contoh harus dibuat atau diganti sesuai struktur nyata. Jika response macro project membungkus resource, pastikan double wrapping tidak terjadi.

## KB-026: Migrations dan schema evolution

- Beri nama migration deskriptif dan gunakan tipe kolom sesuai kebutuhan.
- Tambahkan index untuk kolom yang sering difilter/join; hindari index berlebihan.
- Gunakan foreign keys, unique constraints, dan nullable secara sadar.
- Migration pada database produksi membutuhkan strategi rollback, backup, dan penanganan tabel besar.
- Jangan menjalankan `migrate:fresh`, `db:wipe`, atau migration destruktif tanpa izin pengguna.
- Untuk perubahan kolom dan SQL yang berbeda perilakunya antar DB, cek engine database dan versi Laravel.

Contoh perintah inspeksi (non-destruktif):
```bash
php artisan migrate:status
php artisan route:list
```

## KB-027: Authentication dan authorization

- Authentication memastikan pengguna teridentifikasi; authorization memastikan pengguna boleh mengakses resource.
- Pilih guard sesuai project (session, Sanctum token, Passport, custom).
- Periksa policy dan ownership di backend untuk setiap operasi penting.
- Simpan password hanya sebagai hash melalui fasilitas hashing, jangan plaintext.
- Gunakan rate limiting untuk login dan endpoint sensitif.
- Jangan memaparkan token/access key pada response atau log.
- Dalam multi-tenant app, scope query berdasarkan tenant pada setiap jalur baca/tulis yang relevan.

## KB-028: Errors, exceptions, logging dan observability

Gunakan exception handling standar Laravel dan pertahankan format error yang disepakati project. Jangan menangkap semua exception lalu mengembalikan `200` atau pesan sukses. Jangan bocorkan stack trace, SQL query sensitif, credential, atau PII ke produksi.

```php
use Illuminate\Support\Facades\Log;

Log::warning('Payment callback failed verification', [
    'order_id' => $orderId,
    'provider' => $provider,
]);
```

Contoh ini sengaja tidak menulis payload webhook atau signature. Pastikan variable yang disebut memang tersedia sebelum menggunakannya.

## KB-029: Cache, queue, scheduler, event

Cache data yang sering diakses bila invalidasi jelas; setiap cache harus punya strategi key, TTL, dan invalidation. Background jobs cocok untuk email, notifikasi, integrasi I/O dan operasi lambat. Definisikan retry, backoff, timeout, dan idempotency agar job aman diproses ulang. Pastikan worker aktif di deployment. Untuk pekerjaan setelah transaksi commit, pertimbangkan `afterCommit()` sesuai dukungan versi dan driver. Scheduler perlu proses cron atau scheduler service yang berjalan, tidak cukup mendefinisikan jadwal dalam kode.

## KB-030: File storage dan HTTP client

- Gunakan Storage disk sesuai konfigurasi (`local`, `public`, S3 compatible).
- Validasi mime type, ukuran upload, authorization, dan path sebelum menyimpan file.
- Jangan percaya filename user untuk path filesystem.
- Gunakan `Http::timeout()`, retry terbatas, dan pengecekan response (`throw()`/`successful()`) pada integrasi eksternal.
- Untuk webhook, validasi signature dan ketahanan terhadap replay, sesuai provider.
- Jangan mematikan TLS verification demi menyelesaikan koneksi error.

## KB-031: Testing Laravel dan quality gate

Prioritaskan **feature test** untuk endpoint karena menguji route, middleware, validation, response dan database bersama-sama. Unit test cocok untuk business logic mandiri. Gunakan `RefreshDatabase` pada pengujian database agar state terisolasi; gunakan factories yang tersedia dan jangan jalankan test terhadap database produksi.

```php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_orders(): void
    {
        $this->getJson('/api/orders')->assertUnauthorized();
    }

    public function test_authenticated_user_can_access_orders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/orders')
            ->assertOk();
    }
}
```

Test di atas hanya berlaku jika route/guard/permission dan factory cocok dengan project. Gunakan `php artisan test`, `vendor/bin/pint --test`, static analyzer project, dan test suite CI yang ada. Jangan mengklaim passed bila belum menjalankannya.

## KB-032: PHP dan Laravel tooling

Perintah aman untuk penelusuran:
```bash
php artisan --version
php artisan route:list
php artisan list
composer show --direct
php artisan test --filter=OrdersApiTest
vendor/bin/pint --test
```

Jika ada code intelligence atau stub Intelephense dan project menggunakan response macro custom, editor mungkin menandai `Undefined method 'success'`. Ini bukan bukti runtime pasti gagal. Solusi harus disesuaikan dengan registrasi macro, PHPDoc/stub yang digunakan, dan analisis static. Jangan memodifikasi source code vendor langsung hanya untuk menghilangkan warning editor.

## KB-033: Docker, environment, database, deployment

- Jangan menaruh `.env` atau kredensial produksi dalam RAG.
- Simpan konfigurasi di environment/runtime secrets; `.env.example` boleh menjadi referensi struktur tanpa nilainya.
- Healthcheck harus memeriksa readiness yang relevan, bukan sekadar container berjalan.
- Pisahkan lifecycle PHP app, queue worker, scheduler, database, redis bila sesuai deployment.
- Untuk PostgreSQL container, jalur volume dan entrypoint init berbeda antar major image; ikuti dokumentasi image yang digunakan.
- Pada deployment, pastikan migration terencana, config cache diperbarui, worker direstart secara aman, dan rollback tersedia.
- Jangan melakukan perubahan Docker Compose yang membuka database ke publik tanpa alasan dan firewall.

## KB-034: Praktik keamanan dan performa wajib

1. Otorisasi semua akses resource termasuk yang sudah melewati authentication.
2. Gunakan `$request->validated()` atau data tervalidasi; jangan percaya semua input.
3. Parameter binding/query builder untuk mencegah SQL injection; hati-hati raw expressions.
4. Batasi ukuran query, gunakan pagination dan hindari N+1.
5. Jangan kirim secret, token, atau private data ke log, prompt, dan knowledge base.
6. Hindari `env()` langsung di business logic; gunakan config dan caching yang semestinya.
7. Gunakan transaksi, constraint, dan idempotency untuk pembayaran, inventory dan operasi konkuren.
8. Pahami domain bisnis sebelum memilih nama tabel, enum status, atau mengubah workflow.
9. Perhatikan versi PHP dan kompatibilitas dependency.
10. Buat regression test ketika memperbaiki bug.

## KB-040: Template jawaban coding yang diharapkan

Saat pengguna meminta fitur:
- **Analisis**: kebutuhan, repository yang harus diperiksa, versi framework dan asumsi.
- **File yang terpengaruh**: routes, FormRequest, controller, model/service, resource, migration, test jika memang dibutuhkan.
- **Implementasi**: contoh atau patch minimal mengikuti style existing.
- **Test**: normal case, unauthorized, forbidden, validation failure, not found, transaction/edge cases yang relevan.
- **Perintah verifikasi**: berikan command yang bisa dijalankan tanpa efek destruktif.
- **Catatan**: ketidakpastian sumber dan informasi yang masih dibutuhkan.

Saat pengguna bertanya tentang API Laravel, jawab berdasarkan versi project; bila tak diketahui, nyatakan versi asumsi dan rujuk dokumentasi resmi yang tepat.

## KB-041: Contoh permintaan dan perilaku jawaban

Pertanyaan: "Buat endpoint untuk membuat order".
Jawaban yang benar: cek routes, request, model, hubungan item, auth, response macro, migration, harga dan stok, lalu usulkan implementasi file per file dan test. Jangan membuat model `Order` yang diasumsikan sudah ada. Hindari menyalin skeleton tanpa validasi bisnis.

Pertanyaan: "Bagaimana mengatasi undefined method response()->success()?".
Jawaban yang benar: verifikasi registrasi Response macro; bedakan error Intelephense/static analysis dengan runtime; jangan ubah vendor; tawarkan stub/PHPDoc atau helper yang sesuai setelah melihat implementasi macro.

Pertanyaan: "Apa bedanya with() dan load()?".
Jawaban yang benar: `with()` mendefinisikan eager loading sebelum query model dijalankan; `load()` memuat relation pada model/collection yang sudah diambil. Berikan contoh sesuai kode pengguna.

Pertanyaan: "Buat healthcheck Laravel dalam Docker Compose".
Jawaban yang benar: tentukan apakah healthcheck melalui HTTP endpoint, process check, atau dependensi; pastikan tool seperti `curl` ada di image, dan tidak membuka endpoint sensitif.

## KB-050: Perbaikan dan pemutakhiran RAG

- Simpan style pribadi pada dokumen terpisah setelah dianalisis dari source repository.
- Tambahkan modul/domain bisnis, migrations, kontrak API, test, keputusan arsitektur, dan contoh kode yang sudah disetujui.
- Kelompokkan pengetahuan berdasarkan topik agar retrieval tidak tertukar dengan materi lintas versi.
- Jika mengganti embedding model di Open WebUI, lakukan reindex KB; jika mengubah isi dokumen, unggah ulang/sinkronkan dokumen.
- Uji retrieval dengan pertanyaan yang jawabannya memang tertulis di KB; periksa sumber potongan yang muncul.
- Hindari mengindeks folder `vendor`, `node_modules`, `.git`, `.env`, secrets, dump database, atau file pribadi.

## KB-051: Direktori dokumentasi resmi tambahan per topik

Untuk rincian authoritative, gunakan halaman berdasarkan major version (`13.x` harus diganti bila project berbeda):

- Routing: https://laravel.com/docs/13.x/routing
- Controllers: https://laravel.com/docs/13.x/controllers
- Validation: https://laravel.com/docs/13.x/validation
- Requests: https://laravel.com/docs/13.x/requests
- Responses: https://laravel.com/docs/13.x/responses
- Middleware: https://laravel.com/docs/13.x/middleware
- Container: https://laravel.com/docs/13.x/container
- Service Providers: https://laravel.com/docs/13.x/providers
- Eloquent: https://laravel.com/docs/13.x/eloquent
- Relationships: https://laravel.com/docs/13.x/eloquent-relationships
- Eloquent Resources: https://laravel.com/docs/13.x/eloquent-resources
- Query Builder: https://laravel.com/docs/13.x/queries
- Migrations: https://laravel.com/docs/13.x/migrations
- Authentication: https://laravel.com/docs/13.x/authentication
- Authorization: https://laravel.com/docs/13.x/authorization
- Queues: https://laravel.com/docs/13.x/queues
- Cache: https://laravel.com/docs/13.x/cache
- HTTP Client: https://laravel.com/docs/13.x/http-client
- HTTP Tests: https://laravel.com/docs/13.x/http-tests
- Database Tests: https://laravel.com/docs/13.x/database-testing
- Deployment: https://laravel.com/docs/13.x/deployment
- Sanctum (opsional): https://laravel.com/docs/13.x/sanctum

*Dokumen ini bersifat knowledge tambahan dan pedoman generasi kode, bukan model yang sudah dilatih atau indeks dokumentasi Laravel resmi secara lengkap.*
