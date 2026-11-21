# Universal multi-arch image for MajorDoMo core.
# The official PHP image provides linux/amd64 and linux/arm64 for every tag,
# so the same Dockerfile covers Ubuntu/Windows (x86_64), RPi 4/5 and OrangePC
# (64-bit ARM). Build with `docker buildx build --platform ...` or let Compose
# pick the host platform. A 32-bit RPi OS (arm/v7) is NOT supported.
ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-apache-bookworm

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      libcurl4-openssl-dev \
      libfreetype6-dev \
      libjpeg-dev \
      libpng-dev \
      libzip-dev \
      mariadb-client \
      unzip \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" \
      mysqli curl mbstring gd sockets xml zip \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

COPY docker/apache-site.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-majordomo.ini

WORKDIR /var/www/html
COPY . /var/www/html/

RUN mkdir -p cached cms/cached cms/debmes debmes saverestore modules templates objects rc \
 && chown -R www-data:www-data \
      cached cms debmes saverestore modules templates objects rc

COPY docker/entrypoint.sh /usr/local/bin/mjd-entrypoint
RUN chmod +x /usr/local/bin/mjd-entrypoint

ENV APP_ROLE=web \
    DB_HOST=db \
    DB_NAME=db_terminal \
    DB_USER=mj \
    DB_PASSWORD=mj \
    GENERATE_CONFIG=1

EXPOSE 80
STOPSIGNAL SIGWINCH

ENTRYPOINT ["mjd-entrypoint"]
CMD ["web"]
