# ── Stage 1: JS/CSS assets ──────────────────────────────────────────────────
FROM node:20-alpine AS assets

WORKDIR /app

# Installeer dependencies
COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

# Kopieer alleen bestanden die Vite nodig heeft
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Minimal .env zodat Vite VITE_APP_NAME kan lezen
RUN echo "VITE_APP_NAME=Glavo" > .env-production

RUN npm run build

# ── Stage 2: PHP dependencies ────────────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev

# ── Stage 3: Production image ────────────────────────────────────────────────
FROM php:8.2-fpm-alpine

# System dependencies
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

# PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html

# Copy app files
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

# Set permissions

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
