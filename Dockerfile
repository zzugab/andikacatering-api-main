FROM dunglas/frankenphp:1.5-php8.4-alpine

RUN install-php-extensions pdo_pgsql pgsql bcmath gd zip pcntl
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
RUN mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views && chmod -R 777 storage bootstrap/cache
RUN composer config policy.advisories.block false && composer install --no-interaction --prefer-dist
RUN cp vendor/laravel/octane/src/Commands/stubs/frankenphp-worker.php public/frankenphp-worker.php

RUN cp .env.example .env && php artisan key:generate --force

EXPOSE 8000
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000", "--workers=4"]
