FROM php:8.4-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    default-mysql-client \
    libzip-dev \
    supervisor \
    unzip \
    zip \
    libssl-dev \
    curl \
    nodejs \
    npm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql bcmath sockets zip pcntl \
    && docker-php-ext-enable opcache

RUN groupadd -g 1000 www \
    && useradd -u 1000 -ms /bin/bash -g www www

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --chown=www:www . /var/www/html/

USER www

EXPOSE 80

CMD ["php-fpm"]