FROM composer:2.9.2 AS composer

#####################################################################
# omeka-s-deps is a build stage with dependencies used in both the
# temporary build image and in the final deployment image

FROM php:8.2-apache AS omeka-s-deps

ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt update && apt install -y --no-install-recommends imagemagick

RUN install-php-extensions imagick intl pdo_mysql zip

RUN apt update \
    && apt install -y --no-install-recommends \
       imagemagick gettext-base

#####################################################################
# builder is a build stage which is used by the docker-compose-build.yml
# to spin up a database and omeka, install the distribution modules,
# install the vocabularies and resource templates, and then dump
# the database SQL and the complete PHP app to disk
 
FROM omeka-s-deps AS builder

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt install -y --no-install-recommends \
     git mariadb-client

ARG OMEKA_DIST_URL=https://github.com/Sydney-Informatics-Hub/ngc-omeka
ARG OMEKA_DIST_BRANCH=rc-sih-0.1.2
ARG INSTALL_DIR=/var/www/html

RUN mkdir -p $INSTALL_DIR

RUN git clone -b $OMEKA_DIST_BRANCH $OMEKA_DIST_URL $INSTALL_DIR

WORKDIR $INSTALL_DIR

RUN composer install

RUN mkdir -p $INSTALL_DIR/config

COPY config.tpl $INSTALL_DIR/config/config.tpl
COPY config.json $INSTALL_DIR/config/config.json

# build-entrypoint.sh does the install which requires the server
# and db to be running (modules and resource templates)
# entrypoint is configured in the docker-compose-build.yml
# It also dumps out the database and copies the PHP app to the host

COPY build-entrypoint.sh /build-entrypoint.sh
RUN chmod +x /build-entrypoint.sh

CMD [ "apache-foreground" ]


COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# this is copied from the context, as it contains a bunch of
# things like modules which were created at run time by
# build_entrypoint.sh

COPY assets/php-init /var/www/html

# this is so that we can inject the database password at
# deployment - see the docker-compose.yml

COPY database.ini.tpl /var/www/html/public/config/database.ini.tpl
COPY reset-admin.php /var/www/html/public/reset-admin.php
COPY reset-settings.php /var/www/html/public/reset-settings.php

RUN chown -R www-data:www-data /var/www/html

