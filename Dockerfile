# Stage 1: FPM Base Image
FROM php:8.1-fpm-alpine as base

WORKDIR /var/www

RUN set -xe \
    && apk add --no-cache bash icu-dev \
    && docker-php-ext-install pdo pdo_mysql intl pcntl

CMD ["php-fpm"]

# Stage 2: Composer
FROM base as composer

# Copy the `composer` command from the `composer:latest` image
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN set -xe \
    && rm -rf /var/www  \
    && mkdir /var/www
WORKDIR /var/www

COPY composer.* /var/www/

ARG APP_ENV=prod

RUN set -xe \
    && if [ "$APP_ENV" = "prod" ]; then export ARGS="--no-dev"; fi \
    && composer install --prefer-dist --no-scripts --no-progress --no-interaction $ARGS

# Bring in the application. See `/.dockerignore`.
COPY . /var/www

RUN set -xe \
    && composer dump-autoload --classmap-authoritative

# Stage 3: Our Final Application...
FROM base

ARG APP_ENV=prod
ARG APP_DEBUG=0

ENV APP_ENV $APP_ENV
ENV APP_DEBUG $APP_DEBUG

COPY --from=composer /var/www/ /var/www/

RUN set -xe \
    && php -d memory_limit=1024M bin/console cache:clear \
    && php -d memory_limit=1024M bin/console cache:warm \
    && chown -R www-data:www-data /var/www
