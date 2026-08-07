FROM php:8.2-apache

# Dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libicu-dev \
    autoconf \
    g++ \
    make \
    && rm -rf /var/lib/apt/lists/*

# Extensões PHP
RUN docker-php-ext-install \
    zip \
    intl

# Instala extensão grpc via PECL
RUN pecl install grpc \
    && docker-php-ext-enable grpc

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]