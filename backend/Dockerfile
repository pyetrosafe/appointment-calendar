# Create Composer image
FROM composer:lts as deps
WORKDIR /app
RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=bind,source=composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction

# Create PHP Image and use Composer Image
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html/

# Install dependencies for the operating system software
RUN apt-get update && apt-get install -y \
        libfreetype-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        curl \
        libonig-dev \
        libzip-dev \
    # Install extensions for php
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mbstring zip exif \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && docker-php-ext-enable pdo_mysql \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Configure ini files
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY  ./docker/php.dev.ini /usr/local/etc/php/conf.d/

# Copy existing application directory contents to the working directory
COPY --from=deps app/vendor/ /var/www/html/vendor
COPY ./src /var/www/html
COPY .env.docker .env

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

USER www-data

# Expose port 9000 and start php-fpm server (for FastCGI Process Manager)
EXPOSE 9000

# CMD ["php-fpm"]
CMD ["bash", "-c" , "php-fpm"]

