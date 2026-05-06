FROM composer:2.9.2 AS composer

FROM php:8.2-apache AS builder

# from the php Docker image docks - uses sed to rewrite  /var/www/html
# to something else through all of the config files

ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN a2enmod rewrite

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions imagick intl pdo_mysql zip

COPY --from=composer /usr/bin/composer /usr/bin/composer

ARG OMEKA_DIST_URL=https://github.com/Sydney-Informatics-Hub/ngc-omeka
ARG OMEKA_DIST_BRANCH=feature-single-sign-on
ARG INSTALL_DIR=/var/www/html

RUN apt update \
    && apt install -y --no-install-recommends \
       git gettext-base imagemagick mariadb-client

RUN mkdir -p $INSTALL_DIR


RUN git clone -b $OMEKA_DIST_BRANCH $OMEKA_DIST_URL $INSTALL_DIR

WORKDIR $INSTALL_DIR

RUN composer install

RUN mkdir -p $INSTALL_DIR/config

#FIX ME - use build secrets to interpolate stuff here
# but does it even need them ?

COPY config.tpl $INSTALL_DIR/config/config.tpl

COPY config.json $INSTALL_DIR/config/config.json

# build-entrypoint does the install which requires the server
# and db to be running (modules and resource templates)
# entrypoint is configured in the docker-compose-build.yml

COPY build-entrypoint.sh /build-entrypoint.sh

RUN chmod +x /build-entrypoint.sh

# note to my future self - make this run the container and check
# where docker-entrypoint is coming from

FROM php:8.2-apache AS main

# copies the Omeka S installation from the build stage to
# this one

COPY --from=builder /var/www/html/ /var/www/html




