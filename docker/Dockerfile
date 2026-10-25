FROM php:8.4-fpm-alpine

# Install system dependencies and build libraries
RUN apk add --no-cache \
    curl \
    git \
    bash \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP core and PECL extensions required for Laravel, PostgreSQL, and Redis
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    bcmath \
    pcntl \
    opcache \
    intl \
    mbstring \
    zip

RUN pecl install redis \
    && docker-php-ext-enable redis

# Clean up dev build dependencies to keep container slim
RUN apk del $PHPIZE_DEPS

# Copy Composer binary from official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
