FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    postgresql-client \
    libpq-dev \
    libzip-dev \
    supervisor \
    unzip \
    zip \
    libssl-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_pgsql bcmath sockets zip pcntl \
    && docker-php-ext-enable opcache

RUN pecl install opentelemetry \
    && docker-php-ext-enable opentelemetry

RUN groupadd -g 1000 www \
    && useradd -u 1000 -ms /bin/bash -g www www

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --chown=www:www . /var/www/html/

ARG SUPERVISORD
ENV SUPERVISORD=${SUPERVISORD}

COPY --chown=www:www ./supervisord/${SUPERVISORD}.conf /etc/supervisord.conf

USER www

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]