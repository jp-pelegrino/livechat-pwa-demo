FROM php:8.3-fpm-alpine

# Install build dependencies and libraries required for PHP extensions
RUN apk add --no-cache \
    autoconf \
    g++ \
    gcc \
    make \
    musl-dev \
    libpq-dev \
    icu-dev \
    libzip-dev \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    opcache \
    intl \
    zip \
    mbstring

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies (if composer.json exists)
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
