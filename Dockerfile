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
    && docker-php-ext-install \
        zip \
        intl \
    && rm -rf /var/lib/apt/lists/*

# Instala a extensão gRPC
RUN pecl install grpc \
    && docker-php-ext-enable grpc

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copia os arquivos do Composer primeiro
COPY composer.json composer.lock ./

# Instala exatamente as versões do composer.lock
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# Copia o restante da aplicação
COPY . .

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]