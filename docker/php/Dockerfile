FROM php:8.4-fpm

# Ekstensi PHP yang dibutuhkan Laravel 13 + Filament + Intervention Image
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
        default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        gd \
        intl \
        zip \
        bcmath \
        exif \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-guestbook.ini

# Samakan UID www-data dengan user host supaya file yang dibuat container tidak jadi milik root
ARG UID=1000
ARG GID=1000
RUN groupmod -o -g "${GID}" www-data && usermod -o -u "${UID}" -g "${GID}" www-data

WORKDIR /var/www/html

# HOME yang bisa ditulis: composer, dan psysh (php artisan tinker) menaruh cache di sini.
ENV HOME=/var/www
RUN mkdir -p /var/www/.composer /var/www/.config     && chown -R www-data:www-data /var/www/.composer /var/www/.config /var/www/html

USER www-data

CMD ["php-fpm"]
