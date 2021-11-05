# https://github.com/docker-library/docs/blob/master/php/README.md
FROM php:apache

ARG SEGELFLUG_APP_ENV=production

ENV SEGELFLUG_DB_SYSTEM=mysql \
    SEGELFLUG_DB_HOST=localhost \
    SEGELFLUG_DB_NAME=segelflug_db \
    SEGELFLUG_DB_USER=segelflug_db_user \
    SEGELFLUG_DB_PASS=""

COPY scripts/php-custom.ini $PHP_INI_DIR/conf.d/php-custom.ini
COPY . /var/www/html

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN mv "$PHP_INI_DIR/php.ini-$SEGELFLUG_APP_ENV" "$PHP_INI_DIR/php.ini" && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions @composer intl pdo_mysql && \
    composer install && \
    mv vendor/google/apiclient-services/autoload.php vendor/google/autoload.php && \
    rm -r vendor/google/apiclient-services/* && \
    mv vendor/google/autoload.php vendor/google/apiclient-services/autoload.php