FROM php:8.3-fpm-alpine

# Install build dependencies and libraries required for PHP extensions
# Using --update to refresh package index and retry logic for network issues
RUN apk update && apk add --no-cache \
    autoconf \
    g++ \
    gcc \
    make \
    musl-dev \
    libpq-dev \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    git \
    dos2unix \
    || (sleep 5 && apk update && apk add --no-cache \
    autoconf g++ gcc make musl-dev libpq-dev icu-dev libzip-dev oniguruma-dev git dos2unix)

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    opcache \
    intl \
    zip \
    mbstring

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy entrypoint script and ensure Unix line endings
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN dos2unix /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
