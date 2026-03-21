# Usa FrankenPHP come base
FROM dunglas/frankenphp:1-php8.4 AS base

USER root
ENV PHP_INI_DIR="/usr/local/etc/php"

# Installazione pacchetti di sistema
RUN apt-get update && apt-get install -y \
    autoconf unzip libpq-dev g++ make ncurses-bin git \
    libjpeg-dev libpng-dev libtiff-dev libgif-dev libzstd-dev \
    jpegoptim optipng pngquant gifsicle libavif-bin \
    poppler-utils xfonts-base xfonts-75dpi fontconfig xz-utils \
    net-tools postgresql-client vim \
    && rm -rf /var/lib/apt/lists/*

# Estensioni PHP tramite install-php-extensions
RUN install-php-extensions \
    pdo_pgsql pgsql gd intl bcmath pcntl exif \
    zip opcache imagick redis igbinary apcu pcov

# Installazione composer
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm -f composer-setup.php

# Configurazioni Caddyfile
COPY .build/frankenphp/Caddyfile /etc/caddy/

# Working dir per Laravel
WORKDIR /var/www

# Configurazioni php.ini personalizzate
COPY .build/frankenphp/development.ini $PHP_INI_DIR/conf.d/

FROM base AS development

COPY .build/frankenphp/docker-entrypoint.dev.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]

FROM base AS opencode

# Node.js + npm SOLO QUI
RUN apt-get update && apt-get install -y \
    nodejs npm \
    && rm -rf /var/lib/apt/lists/*

RUN npm install -g opencode-ai

COPY .build/opencode/prompts /tmp/prompts
COPY .build/opencode/config /root/.config/opencode

COPY .build/opencode/docker-entrypoint.opencode.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]



