# Andika Sari Catering API

Backend API untuk aplikasi Andika Sari Catering. Project ini memakai Laravel 10, Sanctum, PostgreSQL, Octane, dan FrankenPHP.

## Stack

- PHP 8.1+
- Laravel 10
- PostgreSQL
- Laravel Sanctum
- Laravel Octane
- FrankenPHP
- Composer
- Docker Compose

## Struktur Project

- `app/` - kode utama Laravel
- `routes/api.php` - route API
- `database/migrations/` - struktur database
- `database/seeders/` - data awal
- `public/` - document root Laravel
- `storage/` - log, cache, upload
- `Dockerfile` - image backend FrankenPHP
- `docker-compose.yml` - service backend port `8000`

## Endpoint Dasar

Base URL local:

```sh
http://localhost:8000/api
```

Endpoint publik utama:

```sh
POST /api/login
POST /api/register
GET /api/landingPage
POST /api/sendForgotpassword
```

Endpoint lain memakai autentikasi Sanctum.

## Install Docker di Local

### Windows atau macOS

1. Download Docker Desktop:

```sh
https://www.docker.com/products/docker-desktop/
```

2. Install Docker Desktop.

3. Buka Docker Desktop sampai status `Docker is running`.

4. Cek instalasi:

```sh
docker --version
docker compose version
```

### Linux Ubuntu/Debian

Update package:

```sh
sudo apt update
sudo apt install -y ca-certificates curl gnupg
```

Tambah Docker GPG key:

```sh
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg
```

Tambah repository Docker:

```sh
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo $VERSION_CODENAME) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
```

Install Docker:

```sh
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

Aktifkan Docker:

```sh
sudo systemctl enable docker
sudo systemctl start docker
```

Agar bisa pakai Docker tanpa `sudo`:

```sh
sudo usermod -aG docker $USER
```

Logout lalu login lagi.

Cek instalasi:

```sh
docker --version
docker compose version
```

## Run Local Tanpa Docker

Masuk folder API:

```sh
cd andikacatering-api
```

Install dependency:

```sh
composer install
```

Buat file `.env`:

```env
APP_NAME="Andika Sari Catering API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nama_database
DB_USERNAME=nama_user
DB_PASSWORD=password_database

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

SANCTUM_STATEFUL_DOMAINS=localhost:4173,127.0.0.1:4173
FRONTEND_URL=http://localhost:4173
```

Generate app key:

```sh
php artisan key:generate
```

Clear cache dan link storage:

```sh
php artisan optimize:clear
php artisan storage:link
```

Migrasi database:

```sh
php artisan migrate
```

Jika butuh data awal:

```sh
php artisan db:seed
```

Jalankan API:

```sh
php artisan serve --host=0.0.0.0 --port=8000
```

API aktif di:

```sh
http://localhost:8000/api
```

## Run Local Dengan Docker

Masuk folder API:

```sh
cd andikacatering-api
```

Pastikan file `.env` sudah ada dan konfigurasi database benar.

Build dan jalankan container:

```sh
docker compose up -d --build
```

Cek log:

```sh
docker compose logs -f backend
```

Jalankan migrasi:

```sh
docker compose exec backend php artisan migrate
```

Jika butuh seed:

```sh
docker compose exec backend php artisan db:seed
```

Cek API:

```sh
curl http://localhost:8000/api/landingPage
```

Stop container:

```sh
docker compose down
```

## Install Docker di Server

Contoh untuk Ubuntu/Debian server.

Update server:

```sh
sudo apt update
sudo apt upgrade -y
```

Install dependency Docker:

```sh
sudo apt install -y ca-certificates curl gnupg ufw
```

Tambah GPG key Docker:

```sh
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg
```

Tambah repository Docker:

```sh
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo $VERSION_CODENAME) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
```

Install Docker Engine dan Compose plugin:

```sh
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

Aktifkan service Docker:

```sh
sudo systemctl enable docker
sudo systemctl start docker
```

Tambahkan user ke group Docker:

```sh
sudo usermod -aG docker $USER
```

Logout lalu login ulang, atau jalankan:

```sh
newgrp docker
```

Cek Docker:

```sh
docker run hello-world
docker compose version
```

Buka firewall port API jika langsung expose port `8000`:

```sh
sudo ufw allow 8000/tcp
```

Jika pakai Nginx reverse proxy, cukup buka HTTP/HTTPS:

```sh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

## Deploy Server Dengan Docker

Clone project:

```sh
git clone <repo-url>
cd <repo-folder>/andikacatering-api
```

Buat `.env` production:

```env
APP_NAME="Andika Sari Catering API"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://api.domain-anda.com

DB_CONNECTION=pgsql
DB_HOST=host_database
DB_PORT=5432
DB_DATABASE=nama_database
DB_USERNAME=nama_user
DB_PASSWORD=password_database

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

SANCTUM_STATEFUL_DOMAINS=domain-frontend-anda.com
FRONTEND_URL=https://domain-frontend-anda.com
```

Generate `APP_KEY` jika belum punya:

```sh
docker run --rm -v "$PWD":/app -w /app composer:latest composer install --ignore-platform-reqs
php artisan key:generate --show
```

Salin output ke `APP_KEY` di `.env`.

Build dan start API:

```sh
docker compose up -d --build
```

Migrasi database production:

```sh
docker compose exec backend php artisan migrate --force
```

Seed jika deployment pertama:

```sh
docker compose exec backend php artisan db:seed --force
```

Optimasi Laravel:

```sh
docker compose exec backend php artisan config:cache
docker compose exec backend php artisan route:cache
docker compose exec backend php artisan view:cache
```

Cek container:

```sh
docker compose ps
docker compose logs -f backend
```

## Reverse Proxy Nginx

Arahkan domain API ke port container `8000`.

```nginx
server {
    server_name api.domain-anda.com;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Cek dan reload Nginx:

```sh
sudo nginx -t
sudo systemctl reload nginx
```

Pasang SSL dengan Certbot:

```sh
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d api.domain-anda.com
```

## Deploy Server Tanpa Docker

Install kebutuhan server:

- PHP 8.1+
- Composer
- PostgreSQL extension: `pdo_pgsql`, `pgsql`
- PHP extension umum: `bcmath`, `gd`, `zip`, `pcntl`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`
- Nginx atau Apache

Install project:

```sh
cd /var/www/andikacatering-api
composer install --no-dev --optimize-autoloader
```

Buat `.env` production lalu generate key:

```sh
php artisan key:generate
```

Permission:

```sh
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Migrasi dan optimasi:

```sh
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Contoh Nginx PHP-FPM:

```nginx
server {
    server_name api.domain-anda.com;
    root /var/www/andikacatering-api/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }
}
```

Reload Nginx:

```sh
sudo nginx -t
sudo systemctl reload nginx
```

## Command Operasional

Clear cache:

```sh
php artisan optimize:clear
```

Cek route:

```sh
php artisan route:list
```

Cek log Laravel:

```sh
tail -f storage/logs/laravel.log
```

Command dalam Docker:

```sh
docker compose exec backend php artisan optimize:clear
docker compose exec backend php artisan route:list
docker compose logs -f backend
```

## Testing

```sh
php artisan test
```

Atau:

```sh
vendor/bin/phpunit
```

## Integrasi Frontend

Local frontend:

```env
API_URL=http://localhost:8000/api
```

Production frontend:

```env
API_URL=https://api.domain-anda.com/api
```

Pastikan CORS dan Sanctum cocok dengan domain frontend.

## Keamanan

- Jangan commit `.env`.
- Jangan tulis password database asli di README.
- Gunakan `APP_DEBUG=false` di production.
- Gunakan HTTPS di server.
- Jalankan migrasi production dengan `--force` setelah backup/validasi.

## Troubleshooting

`could not find driver`:

Pastikan extension PostgreSQL aktif: `pdo_pgsql` dan `pgsql`.

`SQLSTATE connection refused`:

Cek `DB_HOST`, `DB_PORT`, firewall, dan akses database dari container/server.

`storage/logs/laravel.log could not be opened`:

Perbaiki permission:

```sh
chmod -R 775 storage bootstrap/cache
```

`Unauthenticated`:

Kirim token Sanctum:

```sh
Authorization: Bearer <token>
```

`CORS error`:

Pastikan domain frontend masuk konfigurasi CORS/Sanctum dan `API_URL` benar.
