# Create Composer image
FROM composer:lts AS deps
WORKDIR /app
RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=bind,source=composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction --no-autoloader

# Create PHP Image and use Composer Image
FROM php:8.2-fpm AS base

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

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure ini files
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY ./docker/php.dev.ini /usr/local/etc/php/conf.d/

RUN <<EOF
useradd -s /bin/bash -m vscode
groupadd docker
usermod -aG docker vscode
EOF

# install Docker tools (cli, buildx, compose)
COPY --from=gloursdocker/docker / /

FROM base AS copies

# Copy existing application directory contents to the working directory
COPY --from=deps app/vendor/ /var/www/html/vendor
COPY --from=deps /usr/bin/composer /usr/bin/composer

RUN mkdir /var/www/html/src

COPY ./composer.json ./composer.lock /var/www/html/
COPY .env.docker .env

RUN composer dump-autoload

# Expose port 9000 and start php-fpm server (for FastCGI Process Manager)
EXPOSE 9000

# USER www-data
USER vscode

CMD ["bash", "-c" , "php-fpm"]

FROM copies AS xdebug

USER root

# Install and config Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY ./docker/xdebug.ini "$PHP_INI_DIR/conf.d/99-xdebug.ini"

# USER www-data
USER vscode

CMD ["bash", "-c" , "php-fpm"]

