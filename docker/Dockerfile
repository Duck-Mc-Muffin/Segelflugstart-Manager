# https://github.com/docker-library/docs/blob/master/php/README.md
FROM php:apache

# Set app environment [production/development]
ARG SEGELFLUG_APP_ENV=production

# App database connection values
ENV SEGELFLUG_DB_SYSTEM=mysql \
    SEGELFLUG_DB_HOST=localhost \
    SEGELFLUG_DB_NAME=segelflug_db \
    SEGELFLUG_DB_USER=segelflug_db_user \
    SEGELFLUG_DB_PASS=""

# Copy and download projekt files
COPY scripts/php-custom.ini $PHP_INI_DIR/conf.d/php-custom.ini
COPY . /var/www/html
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install php extensions, run composer and remove unnecessary Google API services
RUN mv "$PHP_INI_DIR/php.ini-$SEGELFLUG_APP_ENV" "$PHP_INI_DIR/php.ini" && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions @composer intl pdo_mysql && \
    composer install

# Clean up unnecessary files if this build is a production environment
RUN [ "$SEGELFLUG_APP_ENV" = "production" ] && ./clean_up.sh || echo "clean_up.sh will not be executed"