FROM composer:2.9.2 AS composer

FROM php:8.2-apache AS main

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

RUN apt update
RUN apt install -y git
RUN apt install -y gettext-base

RUN mkdir -p $INSTALL_DIR


RUN git clone -b $OMEKA_DIST_BRANCH $OMEKA_DIST_URL $INSTALL_DIR


WORKDIR $INSTALL_DIR

RUN composer install

RUN mkdir -p $INSTALL_DIR/config
COPY config.tpl $INSTALL_DIR/config/config.tpl

COPY docker-entrypoint.sh /docker-entrypoint.sh

RUN chmod +x /docker-entrypoint.sh
ENTRYPOINT [ "/docker-entrypoint.sh" ]

CMD [ "apache2-foreground" ]

