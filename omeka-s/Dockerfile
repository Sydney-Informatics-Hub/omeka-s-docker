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

#####################################################################
# builder is a build stage which is used by the docker-compose-build.yml
# to spin up a database and omeka, install the distribution modules,
# install the vocabularies and resource templates, and then dump
# the database SQL and the complete PHP app to disk
 
FROM omeka-s-deps AS builder

COPY --from=composer /usr/bin/composer /usr/bin/composer

ARG OMEKA_DIST_URL=https://github.com/Sydney-Informatics-Hub/ngc-omeka
ARG OMEKA_DIST_BRANCH=feature-single-sign-on
ARG INSTALL_DIR=/var/www/html

RUN apt update && apt install -y --no-install-recommends \
    git gettext-base mariadb-client

RUN mkdir -p $INSTALL_DIR

RUN git clone -b $OMEKA_DIST_BRANCH $OMEKA_DIST_URL $INSTALL_DIR

WORKDIR $INSTALL_DIR

RUN composer install

RUN mkdir -p $INSTALL_DIR/config

#FIX ME - use build secrets to interpolate stuff here
# but does it even need them ?

COPY config.tpl $INSTALL_DIR/config/config.tpl

COPY config.json $INSTALL_DIR/config/config.json

# build-entrypoint.sh does the install which requires the server
# and db to be running (modules and resource templates)
# entrypoint is configured in the docker-compose-build.yml
# It also dumps out the database and copies the PHP app to the host

COPY build-entrypoint.sh /build-entrypoint.sh

RUN chmod +x /build-entrypoint.sh

CMD [ "apache-foreground" ]


#####################################################################
# main is the build stage which produces the image for final
# deployment. It starts from omeka-s-deps so that it gets the
# dependencies required by all stages and copies the PHP app which
# has all of the modules into /var/www/html . Initialising the database
# from the SQL dump in this version is handled by the
# docker-compose-dev.yml script

FROM omeka-s-deps AS main

COPY php-init /var/www/html

RUN chown -R www-data:www-data /var/www/html

