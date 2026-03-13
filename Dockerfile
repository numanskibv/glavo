# ── Stage 1: PHP dependencies ────────────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./

# --no-scripts voorkomt dat package:discover een bootstrap/cache dir nodig heeft
# --optimize-autoloader vervangt de aparte dump-autoload stap
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

# ── Stage 2: JS/CSS assets ──────────────────────────────────────────────────
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Flux CSS zit in vendor — nodig voor Tailwind/Vite om app.css te resolven
COPY --from=vendor /app/vendor ./vendor

RUN echo "VITE_APP_NAME=Glavo" > .env
RUN npm run build

# ── Stage 3: Production image ────────────────────────────────────────────────
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    supervisor \
    && docker-php-ext-install \
    bcmath \
    gd \
    mbstring \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    zip \
    && rm -rf /var/cache/apk/*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p bootstrap/cache storage/logs storage/framework/cache storage/framework/sessions storage/framework/views storage/framework/testing \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
