FROM php:8.1-fpm-bookworm

ARG NODE_MAJOR=22

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        default-mysql-client \
        git \
        gnupg \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpq-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        postgresql-client \
        unzip \
        zip \
    && curl -fsSL https://deb.nodesource.com/setup_${NODE_MAJOR}.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        pdo_pgsql \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN cp .env.example .env \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && php artisan package:discover --ansi \
    && npm run build \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && rm -f .env \
    && rm -rf /opt/app-template \
    && mkdir -p /opt/app-template \
    && cp -a /var/www/html/. /opt/app-template/

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/scripts/app-entrypoint.sh /usr/local/bin/docker-app-entrypoint.sh
COPY docker/scripts/vite-entrypoint.sh /usr/local/bin/docker-vite-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-app-entrypoint.sh /usr/local/bin/docker-vite-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-app-entrypoint.sh"]

EXPOSE 8080

CMD ["sh", "-lc", "exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
